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

        $url = '/v1beta/models/gemini-flash-latest:generateContent?key=' . $this->apiKey;

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
                'maxOutputTokens' => 2048,
            ]
        ];

        try {
            $response = $this->client->post($url, [
                'json' => $payload,
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
    protected function logUsage(?int $contentId, ?int $userId, string $fitur, string $prompt, string $output)
    {
        $this->logModel->insert([
            'content_id' => ($contentId && $contentId > 0) ? $contentId : null,
            'user_id' => ($userId && $userId > 0) ? $userId : null,
            'fitur' => $fitur,
            'prompt_input' => $prompt,
            'output' => $output,
            'created_at' => date('Y-m-d H:i:s'),
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
                'content_id' => $konten['id'],
                'status_lama' => 'in_design', // karena AI ini nge-trigger pas masuk review_design
                'status_baru' => 'review_design',
                'user_id' => null, // Sistem
                'catatan' => "[AI Pre-Review Checklist]\n" . $output,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // =========================================================================
    // FITUR: AI HOOK GENERATOR
    // =========================================================================
    public function generateHooks(string $topik, string $platform, int $userId = 0): string
    {
        $prompt = "Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong buatkan 5 contoh kalimat pembuka (Viral Hook 3 detik pertama) yang sangat menarik dan terbukti efektif untuk konten {$platform} berdasarkan topik/produk: \"{$topik}\".\n\n";
        $prompt .= "Untuk setiap hook, berikan:\n";
        $prompt .= "1. Kalimat Hook (Viral Opening)\n";
        $prompt .= "2. Tipe Hook (misal: Curiosity, Fear of Missing Out, Problem-Solving, Bold Statement)\n";
        $prompt .= "3. Alasan singkat kenapa hook ini efektif\n\n";
        $prompt .= "Format output dengan rapi, singkat, dan siap pakai.";

        $output = $this->callGemini($prompt);

        $this->logUsage(null, $userId, 'hook_gen', $prompt, $output);

        return trim($output);
    }

    // =========================================================================
    // FITUR 9.1: AI IDEA GENERATOR
    // =========================================================================
    public function generateIdeas(string $topik, string $platform, int $userId = 0): string
    {
        $prompt = "Kamu adalah Strategis Konten Media Sosial berpengalaman. Tolong berikan 3 ide konten kreatif untuk platform {$platform} berdasarkan topik/produk: \"{$topik}\".\n\n";
        $prompt .= "Untuk setiap ide, berikan:\n";
        $prompt .= "1. Judul Konten yang Menarik\n";
        $prompt .= "2. Konsep/Visual Ringkas\n";
        $prompt .= "3. Call to Action (CTA)\n\n";
        $prompt .= "Format output dengan rapi, singkat, dan mudah dipahami.";

        $output = $this->callGemini($prompt);

        $this->logUsage(null, $userId, 'idea_gen', $prompt, $output);

        return trim($output);
    }

    // =========================================================================
    // FITUR: AI BRIEF GENERATOR
    // =========================================================================
    public function generateBrief(string $judul, string $jenis = '', string $pillar = '', int $userId = 0): string
    {
        $prompt = "Kamu adalah Strategis Konten & Creative Director Media Sosial. Tolong buatkan deskripsi / brief ide singkat dan jelas untuk sebuah ide konten dengan detail berikut:\n\n";
        $prompt .= "- Judul / Topik Konten: \"{$judul}\"\n";
        if (! empty($jenis))  { $prompt .= "- Format / Jenis Konten: {$jenis}\n"; }
        if (! empty($pillar)) { $prompt .= "- Content Pillar: {$pillar}\n"; }
        $prompt .= "\nTuliskan brief ide yang praktis untuk Designer & Copywriter yang mencakup:\n";
        $prompt .= "1. Konsep Visual & Angle Konten\n";
        $prompt .= "2. Poin Utama Pesan / Poin Diskusi\n";
        $prompt .= "3. Output / Call to Action (CTA) singkat\n\n";
        $prompt .= "PENTING ATURAN FORMATTING:\n";
        $prompt .= "- JANGAN gunakan simbol markdown sama sekali seperti bintang-bintang (** atau *), pagar (###), atau garis pembatas (---).\n";
        $prompt .= "- Gunakan format teks polos (plain text) yang rapi, berpenomoran (1, 2, 3), serta simbol bullet sederhana (- atau •) agar bersih saat dibaca di dalam kolom input teks (textarea).\n";
        $prompt .= "- Tulis langsung ke inti brief tanpa kata pembuka informal.";

        $output = $this->callGemini($prompt);

        // Pembersihan otomatis agar tidak ada karakter markdown tersisa di textarea
        $cleanOutput = preg_replace('/[\*#_]{1,3}/', '', $output);
        $cleanOutput = preg_replace('/^\s*[-─_]{3,}\s*$/m', '', $cleanOutput);
        $cleanOutput = trim(preg_replace("/\n{3,}/", "\n\n", $cleanOutput));

        $this->logUsage(null, $userId, 'brief_gen', $prompt, $cleanOutput);

        return $cleanOutput;
    }

    // =========================================================================
    // FITUR: AI REAL-TIME TREND RADAR DISCOVERY
    // =========================================================================
    /**
     * Menghasilkan kurasi tren terkini berdasarkan nama & kategori bisnis.
     */
    public function discoverTrends(string $namaBisnis = '', string $kategoriBisnis = '', string $platform = 'TikTok & Reels', int $userId = 0): array
    {
        $prompt = "Kamu adalah Peneliti Tren Media Sosial & Viral Content Strategist di Indonesia. " .
                  "Hari ini tanggal " . date('d F Y') . ". " .
                  "Tolong carikan dan kurasi 4 sampai 5 TREN KONTEN MEDIA SOSIAL TERKINI & PALING RELEVAN untuk brand/bisnis berikut:\n" .
                  "- Nama Bisnis: " . ($namaBisnis ?: 'Brand Lokal') . "\n" .
                  "- Niche / Kategori Industri: " . ($kategoriBisnis ?: 'Umum / Retail / F&B / Jasa / Fashion') . "\n" .
                  "- Target Platform: " . $platform . "\n\n" .
                  "Berikan respon HANYA dalam format valid JSON (array of objects) tanpa kata pengantar atau markdown block lainnya. Setiap object HARUS memiliki keys:\n" .
                  "[\n" .
                  "  {\n" .
                  "    \"judul\": \"Nama Sound / Format Tren (contoh: Sound Tren 'Jedag Jedug Estetik' / Format 'POV: Kamu nemuin...')\",\n" .
                  "    \"badge\": \"Badge Kategori (pilih salah satu: 'Audio Viral' / 'Format FYP' / 'Hook Trend' / 'CapCut Trend' / 'POV Format')\",\n" .
                  "    \"category\": \"Platform (contoh: 'TikTok, Reels & Linkedin')\",\n" .
                  "    \"desk\": \"Penjelasan tren & kenapa ini sedang viral/ramai saat ini (1-2 kalimat)\",\n" .
                  "    \"example\": \"Contoh implementasi konsep / hook spesifik untuk produk bisnis ini\"\n" .
                  "  }\n" .
                  "]";

        $output = $this->callGemini($prompt);
        $this->logUsage(null, $userId, 'trend_radar', $prompt, $output);

        // Parsing JSON dari AI
        $cleanJson = preg_replace('/^```json\s*/i', '', trim($output));
        $cleanJson = preg_replace('/```$/', '', trim($cleanJson));
        $cleanJson = trim($cleanJson);

        $parsed = json_decode($cleanJson, true);
        if (is_array($parsed) && !empty($parsed)) {
            return $parsed;
        }

        // Fallback jika format JSON AI tidak terbaca sempurna
        return [
            [
                'judul' => 'Tren Storytelling "A Day in My Life as Brand Owner"',
                'badge' => 'Format FYP',
                'category' => $platform,
                'desk' => 'Format konten behind-the-scenes dengan narasi hangat dan transparan yang membangun kepercayaan pelanggan.',
                'example' => 'Tunjukkan proses persiapan pesanan atau riset produk dengan voiceover natural.'
            ],
            [
                'judul' => 'Format POV: Ketika Menemukan Solusi Terbaik',
                'badge' => 'POV Format',
                'category' => $platform,
                'desk' => 'Transisi ekspresi wajah dari bingung menjadi puas setelah menggunakan solusi dari produk brand.',
                'example' => 'Tampilkan perbandingan Sebelum vs Sesudah dalam 5 detik pertama video.'
            ],
            [
                'judul' => 'Hook Trend: "Jangan beli ini sebelum kamu tahu..."',
                'badge' => 'Hook Trend',
                'category' => $platform,
                'desk' => 'Reverse psychology hook yang memicu rasa penasaran penonton untuk menyimak edukasi produk.',
                'example' => 'Jangan coba promo ini kalau kamu belum siap kebanjiran manfaatnya!'
            ],
            [
                'judul' => 'Template CapCut: Transisi Beat Drop Estetik',
                'badge' => 'CapCut Trend',
                'category' => $platform,
                'desk' => 'Template video sinkronisasi foto produk dengan hentakan musik viral yang memiliki retensi tontonan tinggi.',
                'example' => 'Kombinasikan 5 foto detail produk dengan tempo musik cepat.'
            ]
        ];
    }
}

