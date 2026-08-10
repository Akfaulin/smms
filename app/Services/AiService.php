<?php

namespace App\Services;

use App\Models\AiGenerationLogModel;
use App\Models\ContentStatusLogModel;

class AiService
{
    protected $client;
    protected $apiKey;
    protected $logModel;

    public function __construct()
    {
        $this->client = \Config\Services::curlrequest([
            'baseURI' => 'https://generativelanguage.googleapis.com',
            'timeout' => 30,
        ]);
        
        // Kita ambil dari getenv, jika kosong berarti fitur AI dimatikan/belum dikonfigurasi
        $this->apiKey = getenv('GEMINI_API_KEY');
        $this->logModel = new AiGenerationLogModel();
    }

    /**
     * Panggil Gemini API (Gemini 1.5 Flash)
     */
    protected function callGemini(string $prompt): string
    {
        if (empty($this->apiKey)) {
            return "Fitur AI belum dikonfigurasi. Mohon isi GEMINI_API_KEY di file .env.";
        }

        $url = '/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->apiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            ]
        ];

        try {
            $response = $this->client->post($url, [
                'json'    => $payload,
                'headers' => ['Content-Type' => 'application/json'],
                'http_errors' => false // agar bisa nangkep pesan error API
            ]);

            $body = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                log_message('error', 'Gemini API Error: ' . $response->getBody());
                return "Gagal memanggil API AI. Periksa konfigurasi API Key.";
            }

            if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                return $body['candidates'][0]['content']['parts'][0]['text'];
            }

            return "AI tidak mengembalikan respon yang valid.";
        } catch (\Exception $e) {
            log_message('error', 'CURL Exception: ' . $e->getMessage());
            return "Terjadi kesalahan koneksi ke server AI.";
        }
    }

    /**
     * Log penggunaan AI
     */
    protected function logUsage(int $contentId, int $userId, string $fitur, string $prompt, string $output)
    {
        $this->logModel->insert([
            'content_id'   => $contentId,
            'user_id'      => $userId,
            'fitur'        => $fitur,
            'prompt_input' => $prompt,
            'output'       => $output,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    // =========================================================================
    // FITUR 9.2: CAPTION ASSISTANT
    // =========================================================================
    public function generateCaption(int $contentId, string $judul, string $platform, string $brief, int $userId): string
    {
        $prompt = "Kamu adalah asisten copywriter media sosial profesional. Tolong buatkan 1 draft caption yang menarik (termasuk hashtag yang relevan) berdasarkan informasi berikut:\n\n";
        $prompt .= "- Judul/Topik: " . $judul . "\n";
        $prompt .= "- Platform Target: " . $platform . "\n";
        $prompt .= "- Catatan/Brief: " . $brief . "\n\n";
        $prompt .= "Aturan:\n";
        $prompt .= "1. Sesuaikan gaya bahasa (tone) dengan karakteristik platform ($platform).\n";
        $prompt .= "2. Jangan buat kalimat pembuka seperti 'Tentu, ini dia'. Langsung berikan captionnya.\n";

        $output = $this->callGemini($prompt);

        $this->logUsage($contentId, $userId, 'caption_gen', $prompt, $output);

        return trim($output);
    }

    // =========================================================================
    // FITUR 9.3: PRE-REVIEW CHECKLIST
    // =========================================================================
    /**
     * Dijalankan secara asinkron (via cron) atau sinkron (sebelum transisi selesai).
     * Mengevaluasi konten saat masuk ke status 'review_design'.
     */
    public function preReviewCheck(array $konten): void
    {
        // Fitur AI belum jalan kalau API Key kosong
        if (empty($this->apiKey)) {
            return; 
        }

        $judul = $konten['judul_konten'];
        $brief = $konten['deskripsi'] ?? 'Tidak ada brief';
        $caption = $konten['caption'] ?? 'Tidak ada caption';

        $prompt = "Kamu adalah Manager Media Sosial yang sedang mereview draft konten sebelum disetujui untuk dipublish. Tolong berikan evaluasi singkat (maksimal 3 poin) terhadap draft konten ini:\n\n";
        $prompt .= "Judul Konten: " . $judul . "\n";
        $prompt .= "Brief Ide Awal: " . $brief . "\n";
        $prompt .= "Draft Caption: " . $caption . "\n\n";
        $prompt .= "Fokuskan evaluasi pada:\n1. Kesesuaian caption dengan brief\n2. Ejaan/Tata Bahasa (Typo)\n3. Ajakan bertindak (Call to Action) yang jelas\n\nTulis evaluasimu dalam poin-poin yang padat (jangan terlalu panjang) langsung pada intinya.";

        $output = $this->callGemini($prompt);

        // Jika output bukan pesan error
        if (strpos($output, 'Fitur AI belum') === false && strpos($output, 'Gagal memanggil API') === false && strpos($output, 'kesalahan koneksi') === false) {
            
            // Catat di ai_generation_log
            $this->logUsage($konten['id'], 0, 'prereview', $prompt, $output); // 0 = Sistem

            // Catat di content_status_log sebagai catatan sistem
            $statusLog = new ContentStatusLogModel();
            $statusLog->insert([
                'content_id'  => $konten['id'],
                'status_lama' => 'in_design', // karena AI ini nge-trigger pas masuk review_design
                'status_baru' => 'review_design',
                'user_id'     => null, // Sistem
                'catatan'     => "[🤖 AI Pre-Review Checklist]\n" . $output,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // =========================================================================
    // FITUR 9.1: AI IDEA GENERATOR
    // =========================================================================
    public function generateIdeas(string $topik, string $platform, int $userId): string
    {
        $prompt = "Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform {$platform} berdasarkan topik/produk: \"{$topik}\".\n\n";
        $prompt .= "Untuk setiap ide, berikan:\n";
        $prompt .= "1. Judul Konten yang Menarik\n";
        $prompt .= "2. Konsep/Visual Ringkas\n";
        $prompt .= "3. Call to Action (CTA)\n\n";
        $prompt .= "Format output dengan rapi, singkat, dan mudah dipahami.";

        $output = $this->callGemini($prompt);

        $this->logUsage(0, $userId, 'idea_gen', $prompt, $output);

        return trim($output);
    }
}

