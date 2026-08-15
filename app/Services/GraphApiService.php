<?php

namespace App\Services;

use App\Models\MetaApiLogModel;
use CodeIgniter\HTTP\CURLRequest;
use Config\MetaApi;

/**
 * GraphApiService
 *
 * Service layer untuk integrasi Meta Graph API & Instagram Content Publishing.
 * Mendukung dua jalur API:
 * - graph.facebook.com (auth, token, /me/accounts)
 * - graph.instagram.com (Instagram Login API - content publishing)
 */
class GraphApiService
{
    protected ?CURLRequest $client = null;    // graph.facebook.com
    protected ?CURLRequest $igClient = null;  // graph.instagram.com
    protected MetaApi $config;
    protected ?MetaApiLogModel $logModel;

    protected string $appId;
    protected string $appSecret;
    protected string $apiVersion;
    protected string $igUsername;
    protected string $baseUrl;    // graph.facebook.com base
    protected string $igBaseUrl;  // graph.instagram.com base
    protected ?string $cachedToken = null;

    public function __construct(?CURLRequest $client = null)
    {
        $this->config     = config('MetaApi');
        $this->appId      = $this->config->appId;
        $this->appSecret   = $this->config->appSecret;
        $this->apiVersion  = $this->config->apiVersion;
        $this->igUsername  = $this->config->igUsername;
        $this->baseUrl     = 'https://graph.facebook.com/' . $this->apiVersion;
        $this->igBaseUrl   = 'https://graph.instagram.com/' . $this->apiVersion;

        if (function_exists('curl_init') && function_exists('curl_exec')) {
            // Client untuk graph.facebook.com (token/auth endpoint)
            $this->client = $client ?? \Config\Services::curlrequest([
                'baseURI'     => $this->baseUrl . '/',
                'timeout'     => 30,
                'http_errors' => false,
                'verify'      => false,
            ]);

            // Client terpisah untuk graph.instagram.com (Instagram Login API publishing)
            $this->igClient = \Config\Services::curlrequest([
                'baseURI'     => $this->igBaseUrl . '/',
                'timeout'     => 30,
                'http_errors' => false,
                'verify'      => false,
            ]);
        }

        try {
            $this->logModel = new MetaApiLogModel();
        } catch (\Throwable $e) {
            $this->logModel = null;
        }
    }

    /**
     * a) Auto-generate / Get Access Token (Long-lived User/Page Token or App Token)
     */
    public function getAccessToken(): string
    {
        if (! empty($this->cachedToken)) {
            return $this->cachedToken;
        }

        // Cek jika token manual/user sudah dikonfigurasi di .env
        $configuredUserToken = $this->config->userAccessToken;
        if (! empty($configuredUserToken)) {
            // Jika token Instagram (dimulai dengan IG), skip penukaran Facebook Token
            if (strpos($configuredUserToken, 'IG') === 0) {
                if ($this->validateToken($configuredUserToken)) {
                    $this->cachedToken = $configuredUserToken;
                    return $this->cachedToken;
                }
                // Jika validasi gagal tapi dikonfigurasi, tetap gunakan token ini daripada App Token (yang pasti gagal untuk publish)
                log_message('warning', 'Validasi token Instagram (IG...) gagal, namun tetap digunakan sebagai fallback.');
                $this->cachedToken = $configuredUserToken;
                return $this->cachedToken;
            } else {
                // Token Meta/Facebook Page Token biasa (EA...)
                $longLivedToken = $this->exchangeForLongLivedToken($configuredUserToken);
                $tokenToUse     = $longLivedToken ?: $configuredUserToken;

                if ($this->validateToken($tokenToUse)) {
                    $this->cachedToken = $tokenToUse;
                    return $this->cachedToken;
                }
                // Fallback jika validasi gagal
                log_message('warning', 'Validasi token Meta (EA...) gagal, namun tetap digunakan sebagai fallback.');
                $this->cachedToken = $tokenToUse;
                return $this->cachedToken;
            }
        }

        // Fallback: Generate App Access Token via Client Credentials Grant
        $url = '/oauth/access_token';
        $params = [
            'client_id'     => $this->appId,
            'client_secret' => $this->appSecret,
            'grant_type'    => 'client_credentials',
        ];

        $response = $this->requestApi('GET', $url, $params);

        if (isset($response['data']['access_token'])) {
            $this->cachedToken = $response['data']['access_token'];
            return $this->cachedToken;
        }

        // Default fallback ke App ID|App Secret token format
        $this->cachedToken = $this->appId . '|' . $this->appSecret;
        return $this->cachedToken;
    }

    /**
     * Helper untuk tukar short-lived token ke long-lived token (valid s.d. 60 hari)
     */
    protected function exchangeForLongLivedToken(string $shortLivedToken): ?string
    {
        // Jangan coba menukar token Instagram (IG...) di endpoint Facebook
        if (strpos($shortLivedToken, 'IG') === 0) {
            return $shortLivedToken;
        }

        $url = '/oauth/access_token';
        $params = [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $this->appId,
            'client_secret'     => $this->appSecret,
            'fb_exchange_token' => $shortLivedToken,
        ];

        $res = $this->requestApi('GET', $url, $params);

        if (isset($res['data']['access_token'])) {
            return $res['data']['access_token'];
        }

        return null;
    }

    /**
     * d) Validasi Access Token via Meta Debug Token / /me Endpoint
     */
    public function validateToken(?string $token = null): bool
    {
        $tokenToTest = $token ?? $this->cachedToken ?? $this->config->userAccessToken;
        if (empty($tokenToTest)) {
            return false;
        }

        // Jika token Instagram (dimulai dengan IG), validasi menggunakan endpoint /me di graph.instagram.com
        if (strpos($tokenToTest, 'IG') === 0) {
            $res = $this->requestApi('GET', '/me', [
                'fields'       => 'id,username',
                'access_token' => $tokenToTest,
            ], true); // gunakan graph.instagram.com

            return isset($res['data']['id']);
        }

        $appAccessToken = $this->appId . '|' . $this->appSecret;
        $url            = '/debug_token';
        $params         = [
            'input_token'  => $tokenToTest,
            'access_token' => $appAccessToken,
        ];

        $res = $this->requestApi('GET', $url, $params);

        if (isset($res['data']['data']['is_valid'])) {
            return (bool) $res['data']['data']['is_valid'];
        }

        return false;
    }

    /**
     * c) Get Instagram Business Account ID dari target username ("smm.localinternal")
     */
    public function getInstagramBusinessAccountId(?string $username = null): ?string
    {
        // Shortcut: jika ID sudah dikonfigurasi langsung di .env/Config, skip API lookup
        if (! empty($this->config->igAccountId)) {
            return $this->config->igAccountId;
        }

        $targetUsername = strtolower($username ?? $this->igUsername);
        $token          = $this->getAccessToken();


        // Method A: Query /me/accounts untuk cari FB Page & linked IG Business Account
        $url = '/me/accounts';
        $params = [
            'fields'       => 'id,name,instagram_business_account{id,username}',
            'access_token' => $token,
        ];

        $res = $this->requestApi('GET', $url, $params);

        if (isset($res['data']['data']) && is_array($res['data']['data'])) {
            foreach ($res['data']['data'] as $page) {
                if (isset($page['instagram_business_account'])) {
                    $igAcc = $page['instagram_business_account'];
                    $igUser = strtolower($igAcc['username'] ?? '');

                    if (empty($targetUsername) || $igUser === $targetUsername || strpos($igUser, $targetUsername) !== false) {
                        return (string) $igAcc['id'];
                    }
                }
            }
            // Jika tidak cocok dengan username spesifik tapi ada IG account pertama, return yang ada
            foreach ($res['data']['data'] as $page) {
                if (isset($page['instagram_business_account']['id'])) {
                    return (string) $page['instagram_business_account']['id'];
                }
            }
        }

        // Method B: Query Direct IG Business Accounts / me?fields=instagram_business_account
        $url2 = '/me';
        $params2 = [
            'fields'       => 'instagram_business_account',
            'access_token' => $token,
        ];

        $res2 = $this->requestApi('GET', $url2, $params2);
        if (isset($res2['data']['instagram_business_account']['id'])) {
            return (string) $res2['data']['instagram_business_account']['id'];
        }

        return null;
    }

    /**
     * Konversi link Google Drive share menjadi direct-access URL.
     *
     * Google Drive share link:  https://drive.google.com/file/d/{FILE_ID}/view?usp=sharing
     * Direct access URL:        https://drive.google.com/uc?export=view&id={FILE_ID}
     *
     * Jika URL bukan link Google Drive, dikembalikan apa adanya.
     *
     * @param string $url URL asli (bisa berupa share link Drive atau URL publik lainnya)
     * @return string URL yang sudah dikonversi (atau URL asli jika bukan Drive)
     */
    public function convertDriveLink(string $url): string
    {
        $url = trim($url);
        if (empty($url)) {
            return $url;
        }

        // Deteksi pola Google Drive file: mengandung drive.google.com dan /d/{FILE_ID}
        if (strpos($url, 'drive.google.com') !== false) {
            if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                $fileId = $matches[1];
                return 'https://drive.google.com/uc?export=view&id=' . $fileId;
            }
        }

        // Bukan link Drive — kembalikan apa adanya
        return $url;
    }

    /**
     * Parse input media_url yang bisa berupa single URL, JSON array string, atau newline-separated URLs.
     *
     * @param string $mediaUrl
     * @return array<string>
     */
    public function parseMediaUrls(string $mediaUrl): array
    {
        $mediaUrl = trim($mediaUrl);
        if (empty($mediaUrl)) {
            return [];
        }

        // Cek jika format JSON array
        if (str_starts_with($mediaUrl, '[') && str_ends_with($mediaUrl, ']')) {
            $decoded = json_decode($mediaUrl, true);
            if (is_array($decoded)) {
                $urls = [];
                foreach ($decoded as $item) {
                    $u = trim((string)$item);
                    if (! empty($u)) {
                        $urls[] = $this->convertDriveLink($u);
                    }
                }
                if (! empty($urls)) {
                    return $urls;
                }
            }
        }

        // Cek jika pemisah baris baru atau koma
        if (str_contains($mediaUrl, "\n") || str_contains($mediaUrl, "\r")) {
            $lines = preg_split('/[\r\n]+/', $mediaUrl);
            $urls  = [];
            foreach ($lines as $line) {
                $u = trim($line);
                if (! empty($u)) {
                    $urls[] = $this->convertDriveLink($u);
                }
            }
            if (! empty($urls)) {
                return $urls;
            }
        }

        return [$this->convertDriveLink($mediaUrl)];
    }

    /**
     * Tentukan konfigurasi payload publish Instagram berdasarkan URL media dan jenis konten.
     *
     * @param string $url URL media publik
     * @param string|null $jenisHint Nama jenis konten (misal: 'Story', 'Reels / Video', 'Static Post', 'Carousel')
     * @return array{target: 'STORIES'|'REELS'|'IMAGE'|'CAROUSEL', is_video: bool, urls: array<string>, label: string}
     */
    public function resolvePublishTarget(string $url, ?string $jenisHint = null): array
    {
        $jenisLower = strtolower($jenisHint ?? '');
        $isStory    = (strpos($jenisLower, 'story') !== false || strpos($jenisLower, 'stories') !== false);
        $isReels    = (strpos($jenisLower, 'reels') !== false || strpos($jenisLower, 'video') !== false);
        $isCarousel = (strpos($jenisLower, 'carousel') !== false || strpos($jenisLower, 'slider') !== false);

        $urls = $this->parseMediaUrls($url);
        $firstUrl = $urls[0] ?? $url;

        // Jika jenis Carousel atau memiliki lebih dari 1 file media
        if ($isCarousel || count($urls) > 1) {
            $slideCount = count($urls);
            return [
                'target'   => 'CAROUSEL',
                'is_video' => false,
                'urls'     => $urls,
                'label'    => "Instagram Carousel ({$slideCount} Slide)",
            ];
        }

        $isVideo = false;

        // 1. Cek ekstensi file umum dari URL
        if (preg_match('/\.(mp4|mov|avi|webm|mkv|m4v)(\?.*)?$/i', $firstUrl)) {
            $isVideo = true;
        } elseif (preg_match('/\.(jpe?g|png|webp|gif|bmp)(\?.*)?$/i', $firstUrl)) {
            $isVideo = false;
        } else {
            // 2. Cek Content-Type via get_headers / cURL HEAD jika ekstensi tidak eksplisit (misal link Google Drive)
            try {
                if (function_exists('curl_init') && function_exists('curl_exec')) {
                    $ch = @\curl_init();
                    if ($ch) {
                        @\curl_setopt($ch, CURLOPT_URL, $firstUrl);
                        @\curl_setopt($ch, CURLOPT_NOBODY, true);
                        @\curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        @\curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                        @\curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                        @\curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        @\curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        @\curl_exec($ch);
                        $contentType = @\curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                        @\curl_close($ch);

                        if ($contentType) {
                            $ctLower = strtolower($contentType);
                            if (str_starts_with($ctLower, 'video/')) {
                                $isVideo = true;
                            } elseif (str_starts_with($ctLower, 'image/')) {
                                $isVideo = false;
                            }
                        }
                    }
                } else {
                    $headers = @\get_headers($firstUrl, true);
                    $ct = $headers['Content-Type'] ?? ($headers['content-type'] ?? null);
                    if (is_array($ct)) {
                        $ct = end($ct);
                    }
                    if ($ct && is_string($ct)) {
                        $ctLower = strtolower($ct);
                        if (str_starts_with($ctLower, 'video/')) {
                            $isVideo = true;
                        } elseif (str_starts_with($ctLower, 'image/')) {
                            $isVideo = false;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $isVideo = $isReels;
            }
        }

        // 3. Tentukan Target Publishing Instagram
        if ($isStory) {
            return [
                'target'   => 'STORIES',
                'is_video' => $isVideo,
                'urls'     => $urls,
                'label'    => $isVideo ? 'Instagram Story (Video)' : 'Instagram Story (Gambar)',
            ];
        }

        if ($isReels || $isVideo) {
            return [
                'target'   => 'REELS',
                'is_video' => true,
                'urls'     => $urls,
                'label'    => 'Instagram Reels',
            ];
        }

        return [
            'target'   => 'IMAGE',
            'is_video' => false,
            'urls'     => $urls,
            'label'    => 'Instagram Feed (Gambar)',
        ];
    }

    /**
     * Helper backward-compatible untuk deteksi tipe media 'VIDEO' atau 'IMAGE'
     */
    public function detectMediaType(string $url, ?string $jenisHint = null): string
    {
        $target = $this->resolvePublishTarget($url, $jenisHint);
        return $target['is_video'] ? 'VIDEO' : 'IMAGE';
    }

    /**
     * b) Publish media (Gambar, Reels Video, Instagram Story, atau Carousel Multi-Slide) ke Instagram
     *
     * @param string $mediaUrl URL publik gambar, video, atau JSON array URL untuk Carousel
     * @param string $caption Caption postingan
     * @param string|null $mediaTypeHint Petunjuk tipe konten / jenis konten ('Story', 'Reels / Video', 'Static Post', 'Carousel', dsb)
     * @return array Standard response format ['status' => 'sukses'|'gagal', 'pesan' => string, 'data' => array]
     */
    public function publishToInstagram(string $mediaUrl, string $caption = '', ?string $mediaTypeHint = null): array
    {
        // 1. Validasi Media URL
        $urlValidation = $this->validatePublicUrl($mediaUrl);
        if ($urlValidation['status'] === 'gagal') {
            return $urlValidation;
        }

        // 2. Validasi Caption (Max 2200 Karakter)
        $warning = null;
        if (empty(trim($caption))) {
            $warning = 'Caption kosong. Media akan dipublish tanpa caption.';
        } elseif (mb_strlen($caption) > 2200) {
            $caption = mb_substr($caption, 0, 2200);
            $warning = 'Caption melebihi 2200 karakter dan telah dipotong secara otomatis.';
        }

        // 3. Dapatkan Instagram Business Account ID
        $igAccountId = $this->getInstagramBusinessAccountId();

        if (empty($igAccountId)) {
            return [
                'status' => 'gagal',
                'pesan'  => 'Gagal mendapatkan Instagram Business Account ID untuk username "' . $this->igUsername . '". Pastikan User Access Token dengan permission instagram_content_publish sudah dikonfigurasi di .env.',
                'data'   => [
                    'media_url'  => $mediaUrl,
                    'ig_account' => $this->igUsername,
                ],
            ];
        }

        $token = $this->getAccessToken();

        // 4. Tentukan target & format publish (STORIES, REELS, CAROUSEL, atau FEED IMAGE)
        $publishConfig = $this->resolvePublishTarget($mediaUrl, $mediaTypeHint);
        $target        = $publishConfig['target'];
        $isVideo       = $publishConfig['is_video'];
        $mediaLabel    = $publishConfig['label'];
        $urls          = $publishConfig['urls'];

        // ---------------------------------------------------------------------
        // CAROUSEL PUBLISHING (Multi-Slide Items -> Parent Container -> Publish)
        // ---------------------------------------------------------------------
        if ($target === 'CAROUSEL') {
            if (empty($urls)) {
                return [
                    'status' => 'gagal',
                    'pesan'  => 'Carousel memerlukan minimal 1 URL media gambar/video.',
                    'data'   => [],
                ];
            }

            $childrenIds = [];
            foreach ($urls as $idx => $itemUrl) {
                $slideNum    = $idx + 1;
                $isItemVideo = (preg_match('/\.(mp4|mov|avi|webm|mkv|m4v)(\?.*)?$/i', $itemUrl) || $this->detectMediaType($itemUrl) === 'VIDEO');

                $childPayload = [
                    'is_carousel_item' => 'true',
                    'access_token'     => $token,
                ];

                if ($isItemVideo) {
                    $childPayload['media_type'] = 'VIDEO';
                    $childPayload['video_url']  = $itemUrl;
                } else {
                    $childPayload['image_url']  = $itemUrl;
                }

                $childRes = $this->requestApiWithRetry('POST', '/' . $igAccountId . '/media', $childPayload, 3, true);

                if ($childRes['status'] !== 'sukses' || empty($childRes['data']['id'])) {
                    $errMsg = $childRes['pesan'] ?? "Gagal memproses container slide ke-{$slideNum}";
                    return [
                        'status' => 'gagal',
                        'pesan'  => "Meta Carousel Slide #{$slideNum} Error: " . $this->sanitizeMessage($errMsg),
                        'data'   => $childRes['data'] ?? [],
                    ];
                }

                $childId = $childRes['data']['id'];

                if ($isItemVideo) {
                    $this->waitForContainerReady($igAccountId, $childId, $token, 60);
                }

                $childrenIds[] = $childId;
            }

            // Step 2: Create Carousel Container (Parent)
            $parentPayload = [
                'media_type'   => 'CAROUSEL',
                'children'     => implode(',', $childrenIds),
                'caption'      => $caption,
                'access_token' => $token,
            ];

            $parentRes = $this->requestApiWithRetry('POST', '/' . $igAccountId . '/media', $parentPayload, 3, true);

            if ($parentRes['status'] !== 'sukses' || empty($parentRes['data']['id'])) {
                $errMsg = $parentRes['pesan'] ?? 'Gagal membuat container Carousel parent.';
                return [
                    'status' => 'gagal',
                    'pesan'  => 'Meta Carousel Parent Error: ' . $this->sanitizeMessage($errMsg),
                    'data'   => $parentRes['data'] ?? [],
                ];
            }

            $creationId = $parentRes['data']['id'];

            // Step 3: Publish Parent Container
            $publishUrl = '/' . $igAccountId . '/media_publish';
            $publishPayload = [
                'creation_id'  => $creationId,
                'access_token' => $token,
            ];

            $publishRes = $this->requestApiWithRetry('POST', $publishUrl, $publishPayload, 3, true);

            if ($publishRes['status'] !== 'sukses' || empty($publishRes['data']['id'])) {
                $errorMessage = $publishRes['pesan'] ?? 'Gagal mempublikasikan Carousel ke Instagram.';
                return [
                    'status' => 'gagal',
                    'pesan'  => 'Meta Graph API Publish Error: ' . $this->sanitizeMessage($errorMessage),
                    'data'   => $publishRes['data'] ?? [],
                ];
            }

            $mediaId = $publishRes['data']['id'];

            return [
                'status' => 'sukses',
                'pesan'  => "Konten {$mediaLabel} berhasil dipublish ke Instagram!",
                'data'   => [
                    'media_id'     => $mediaId,
                    'creation_id'  => $creationId,
                    'ig_account_id'=> $igAccountId,
                    'target_type'  => 'CAROUSEL',
                    'slide_count'  => count($childrenIds),
                    'media_url'    => $mediaUrl,
                    'caption'      => $caption,
                    'warning'      => $warning,
                ],
            ];
        }

        // ---------------------------------------------------------------------
        // SINGLE ITEM PUBLISHING (STORIES / REELS / FEED IMAGE)
        // ---------------------------------------------------------------------
        $singleUrl = $urls[0] ?? $this->convertDriveLink($mediaUrl);
        $containerUrl = '/' . $igAccountId . '/media';

        if ($target === 'STORIES') {
            $containerPayload = [
                'media_type'   => 'STORIES',
                'access_token' => $token,
            ];
            if ($isVideo) {
                $containerPayload['video_url'] = $singleUrl;
            } else {
                $containerPayload['image_url'] = $singleUrl;
            }
        } elseif ($target === 'REELS') {
            $containerPayload = [
                'media_type'   => 'REELS',
                'video_url'    => $singleUrl,
                'caption'      => $caption,
                'access_token' => $token,
            ];
        } else {
            // FEED IMAGE
            $containerPayload = [
                'image_url'    => $singleUrl,
                'caption'      => $caption,
                'access_token' => $token,
            ];
        }

        $containerRes = $this->requestApiWithRetry('POST', $containerUrl, $containerPayload, 3, true);

        if ($containerRes['status'] !== 'sukses' || empty($containerRes['data']['id'])) {
            $errorMessage = $containerRes['pesan'] ?? 'Gagal membuat container media Instagram.';
            return [
                'status' => 'gagal',
                'pesan'  => 'Meta Graph API Container Error: ' . $this->sanitizeMessage($errorMessage),
                'data'   => $containerRes['data'] ?? [],
            ];
        }

        $creationId = $containerRes['data']['id'];

        // Tunggu container media selesai diproses Meta (async processing)
        $maxWaitTime = $isVideo ? 90 : 35;
        $readyStatus = $this->waitForContainerReady($igAccountId, $creationId, $token, $maxWaitTime);

        if (! $readyStatus['ready']) {
            return [
                'status' => 'gagal',
                'pesan'  => 'Meta Container Processing Error: ' . ($readyStatus['error'] ?? 'Proses encoding media di server Meta gagal atau timeout.'),
                'data'   => ['creation_id' => $creationId, 'status' => $readyStatus['status'] ?? 'ERROR'],
            ];
        }

        // Publish Container via Instagram Login API
        $publishUrl = '/' . $igAccountId . '/media_publish';
        $publishPayload = [
            'creation_id'  => $creationId,
            'access_token' => $token,
        ];

        $publishRes = $this->requestApiWithRetry('POST', $publishUrl, $publishPayload, 3, true);

        // Handle specific error code 9007: Image/Video processing still pending
        if ($publishRes['status'] === 'gagal' && isset($publishRes['data']['error']['code']) && $publishRes['data']['error']['code'] == 9007) {
            log_message('warning', "Meta Graph API 9007 encountered. Retrying publish in 8 seconds...");
            sleep(8);
            $publishRes = $this->requestApiWithRetry('POST', $publishUrl, $publishPayload, 3, true);
        }

        if ($publishRes['status'] !== 'sukses' || empty($publishRes['data']['id'])) {
            $errorMessage = $publishRes['pesan'] ?? 'Gagal mempublikasikan container media Instagram.';
            return [
                'status' => 'gagal',
                'pesan'  => 'Meta Graph API Publish Error: ' . $this->sanitizeMessage($errorMessage),
                'data'   => $publishRes['data'] ?? [],
            ];
        }

        $mediaId = $publishRes['data']['id'];

        return [
            'status' => 'sukses',
            'pesan'  => "Konten {$mediaLabel} berhasil dipublish ke Instagram!",
            'data'   => [
                'media_id'     => $mediaId,
                'creation_id'  => $creationId,
                'ig_account_id'=> $igAccountId,
                'target_type'  => $target,
                'media_type'   => $isVideo ? 'VIDEO' : 'IMAGE',
                'media_url'    => $singleUrl,
                'caption'      => $caption,
                'warning'      => $warning,
            ],
        ];
    }

    /**
     * Validasi URL publik (mendukung single URL maupun multi URL)
     */
    public function validatePublicUrl(string $url): array
    {
        $urls = $this->parseMediaUrls($url);
        if (empty($urls)) {
            return [
                'status' => 'gagal',
                'pesan'  => 'URL media (image_url) wajib diisi dan tidak boleh kosong.',
                'data'   => [],
            ];
        }

        foreach ($urls as $idx => $singleUrl) {
            $num = count($urls) > 1 ? (" (Slide #" . ($idx + 1) . ")") : "";

            if (! filter_var($singleUrl, FILTER_VALIDATE_URL)) {
                return [
                    'status' => 'gagal',
                    'pesan'  => "Format URL media{$num} tidak valid: " . htmlspecialchars($singleUrl),
                    'data'   => [],
                ];
            }

            $parsed = parse_url($singleUrl);
            $host   = strtolower($parsed['host'] ?? '');

            if (empty($host) || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1' || substr($host, -6) === '.local') {
                return [
                    'status' => 'gagal',
                    'pesan'  => "URL media{$num} harus berupa URL publik yang dapat diakses Meta Graph API. Host localhost/IP lokal '{$host}' tidak didukung oleh Meta.",
                    'data'   => ['host' => $host, 'url' => $singleUrl],
                ];
            }

            $ip = gethostbyname($host);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false && $ip !== $host) {
                return [
                    'status' => 'gagal',
                    'pesan'  => "URL media{$num} menunjuk ke IP private/lokal ({$ip}). Meta API memerlukan URL publik yang bisa diakses internet.",
                    'data'   => ['ip' => $ip, 'host' => $host, 'url' => $singleUrl],
                ];
            }
        }

        return [
            'status' => 'sukses',
            'pesan'  => 'URL publik valid.',
            'data'   => ['count' => count($urls), 'urls' => $urls],
        ];
    }

    /**
     * Tunggu container media selesai diproses Meta sebelum publish.
     * Meta membutuhkan beberapa detik untuk fetch & process gambar / video (async).
     * Poll GET /{container-id}?fields=status_code sampai FINISHED atau timeout.
     *
     * @return array{ready: bool, status: string, error?: string}
     */
    protected function waitForContainerReady(string $igAccountId, string $containerId, string $token, int $maxWait = 60): array
    {
        $waited  = 0;
        $poll    = 4; // interval detik

        while ($waited < $maxWait) {
            sleep($poll);
            $waited += $poll;

            $statusRes = $this->requestApi('GET', '/' . $containerId, [
                'fields'       => 'status_code,status',
                'access_token' => $token,
            ], true); // gunakan graph.instagram.com

            $statusCode = $statusRes['data']['status_code'] ?? '';

            if ($statusCode === 'FINISHED') {
                log_message('info', "Meta container {$containerId} FINISHED after {$waited}s");
                return ['ready' => true, 'status' => 'FINISHED'];
            }

            if ($statusCode === 'ERROR') {
                $err = $statusRes['data']['status'] ?? 'Terjadi kesalahan saat memproses media di server Instagram.';
                log_message('error', "Meta container {$containerId} ERROR: {$err}");
                return ['ready' => false, 'status' => 'ERROR', 'error' => $err];
            }

            if ($statusCode === 'EXPIRED') {
                log_message('error', "Meta container {$containerId} EXPIRED.");
                return ['ready' => false, 'status' => 'EXPIRED', 'error' => 'Container media telah kedaluwarsa sebelum dipublish.'];
            }

            log_message('info', "Meta container {$containerId} status: {$statusCode}. Waiting {$poll}s more ({$waited}/{$maxWait})...");
        }

        log_message('warning', "Meta container {$containerId}: max wait {$maxWait}s reached. Attempting publish anyway.");
        return ['ready' => true, 'status' => 'TIMEOUT_ATTEMPT'];
    }

    /**
     * Wrapper request API dengan Retry Strategy & Exponential Backoff (429 Rate Limit, 401 Retry)
     * @param bool $useIgApi Jika true, gunakan graph.instagram.com. Default false = graph.facebook.com
     */
    protected function requestApiWithRetry(string $method, string $endpoint, array $payload = [], int $maxRetries = 3, bool $useIgApi = false): array
    {

        $attempt = 0;
        $delay   = 1; // detik

        while ($attempt < $maxRetries) {
            $attempt++;
            $result = $this->requestApi($method, $endpoint, $payload, $useIgApi);

            $code = $result['code'] ?? 0;

            // Handle HTTP 429 Rate Limit -> Exponential Backoff
            if ($code === 429) {
                log_message('warning', "Meta Graph API 429 Rate Limit encountered on {$endpoint}. Retrying in {$delay} seconds (Attempt {$attempt}/{$maxRetries})...");
                sleep($delay);
                $delay *= 2;
                continue;
            }

            // Handle HTTP 401 Unauthorized / Token Expired -> Auto-retry dengan refresh token
            if ($code === 401 && $attempt === 1) {
                log_message('warning', "Meta Graph API 401 Unauthorized on {$endpoint}. Re-generating token and retrying...");
                $this->cachedToken = null;
                $payload['access_token'] = $this->getAccessToken();
                continue;
            }

            return $result;
        }

        return [
            'status' => 'gagal',
            'pesan'  => 'Permintaan ke Meta Graph API gagal setelah ' . $maxRetries . ' kali percobaan.',
            'data'   => [],
        ];
    }

    /**
     * Execute HTTP Request to Meta Graph API & log results
     * @param bool $useIgApi Jika true, gunakan graph.instagram.com (Instagram Login API).
     */
    protected function requestApi(string $method, string $endpoint, array $params = [], bool $useIgApi = false): array
    {
        $cleanEndpoint = '/' . ltrim($endpoint, '/');
        $activeBaseUrl = $useIgApi ? $this->igBaseUrl : $this->baseUrl;
        $absoluteUrl   = $activeBaseUrl . $cleanEndpoint;

        $rawBody    = false;
        $statusCode = 0;
        $curlError  = '';

        // 1. Coba native cURL jika ekstensi tersedia
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            try {
                $ch = @\curl_init();
                if ($ch) {
                    @\curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    @\curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    @\curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    @\curl_setopt($ch, CURLOPT_TIMEOUT, 35);

                    if (strtoupper($method) === 'GET') {
                        $getUrl = $absoluteUrl . (!empty($params) ? ('?' . http_build_query($params)) : '');
                        @\curl_setopt($ch, CURLOPT_URL, $getUrl);
                    } else {
                        @\curl_setopt($ch, CURLOPT_URL, $absoluteUrl);
                        @\curl_setopt($ch, CURLOPT_POST, true);
                        @\curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    }

                    $rawBody    = @\curl_exec($ch);
                    $statusCode = (int) @\curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError  = @\curl_error($ch);
                    @\curl_close($ch);
                }
            } catch (\Throwable $e) {
                $curlError = $e->getMessage();
            }
        }

        // 2. Stream fallback jika cURL tidak aktif / dibatasi di cPanel
        if ($rawBody === false) {
            try {
                $isPost    = (strtoupper($method) === 'POST');
                $streamUrl = $absoluteUrl . ((!$isPost && !empty($params)) ? ('?' . http_build_query($params)) : '');

                $httpOptions = [
                    'method'        => strtoupper($method),
                    'timeout'       => 35,
                    'ignore_errors' => true,
                ];

                if ($isPost) {
                    $content = http_build_query($params);
                    $httpOptions['header']  = "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($content) . "\r\n";
                    $httpOptions['content'] = $content;
                }

                $ctx = stream_context_create([
                    'http' => $httpOptions,
                    'ssl'  => [
                        'verify_peer'      => false,
                        'verify_peer_name' => false,
                    ]
                ]);

                $rawBody = @file_get_contents($streamUrl, false, $ctx);
                if (isset($http_response_header[0]) && preg_match('/HTTP\/\S+\s+(\d+)/', $http_response_header[0], $m)) {
                    $statusCode = (int) $m[1];
                }
            } catch (\Throwable $t) {
                $curlError = $t->getMessage();
            }
        }

        if ($rawBody === false) {
            return [
                'status' => 'gagal',
                'code'   => 0,
                'pesan'  => 'Gagal melakukan koneksi ke Meta Graph API (cURL / Stream error): ' . ($curlError ?: 'Koneksi jaringan server terputus.'),
                'data'   => [],
            ];
        }

        $body = json_decode($rawBody, true) ?? [];

            // Sanitasi payload untuk log agar token/secret disamarkan
            $logPayload = $params;
            if (isset($logPayload['access_token'])) {
                $logPayload['access_token'] = substr($logPayload['access_token'], 0, 10) . '...***';
            }
            if (isset($logPayload['client_secret'])) {
                $logPayload['client_secret'] = '***REDACTED***';
            }

            $statusText = ($statusCode >= 200 && $statusCode < 300) ? 'sukses' : 'gagal';

            // Log ke system log & DB log
            $this->logApiCall($cleanEndpoint, $method, $logPayload, $statusCode, $rawBody, $statusText);

            if ($statusText === 'gagal') {
                $errorMsg = $body['error']['message'] ?? ("HTTP " . $statusCode . " Error");
                return [
                    'status' => 'gagal',
                    'code'   => $statusCode,
                    'pesan'  => $errorMsg,
                    'data'   => $body,
                ];
            }

            return [
                'status' => 'sukses',
                'code'   => $statusCode,
                'pesan'  => 'OK',
                'data'   => $body,
            ];
        } catch (\Throwable $e) {
            log_message('error', "CURL Exception Meta API ({$cleanEndpoint}): " . $e->getMessage());

            $this->logApiCall($cleanEndpoint, $method, $params, 500, $e->getMessage(), 'gagal');

            return [
                'status' => 'gagal',
                'code'   => 500,
                'pesan'  => 'Terjadi kesalahan koneksi ke Meta Graph API: ' . $e->getMessage(),
                'data'   => [],
            ];
        }
    }

    /**
     * Catat log panggilan API
     */
    protected function logApiCall(string $endpoint, string $method, array $payload, int $code, string $body, string $status): void
    {
        $logMessage = sprintf('[Meta API Log] [%s] %s %s | Code: %d | Status: %s', date('Y-m-d H:i:s'), strtoupper($method), $endpoint, $code, $status);
        if ($status === 'gagal') {
            log_message('error', $logMessage . ' | Body: ' . substr($body, 0, 300));
        } else {
            log_message('info', $logMessage);
        }

        if ($this->logModel) {
            try {
                $this->logModel->insert([
                    'endpoint'      => $endpoint,
                    'method'        => strtoupper($method),
                    'payload'       => json_encode($payload, JSON_UNESCAPED_SLASHES),
                    'response_code' => $code,
                    'response_body' => substr($body, 0, 1000),
                    'status'        => $status,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $t) {
                log_message('error', 'Gagal menyimpan MetaApiLog: ' . $t->getMessage());
            }
        }
    }

    /**
     * Sanitasi pesan error agar App Secret tidak bocor
     */
    protected function sanitizeMessage(string $msg): string
    {
        if (! empty($this->appSecret)) {
            $msg = str_replace($this->appSecret, '***REDACTED***', $msg);
        }
        return $msg;
    }
}
