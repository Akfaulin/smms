<?php

namespace App\Commands;

use App\Services\AutoPublishService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AutoPublishContent extends BaseCommand
{
    protected $group       = 'SMMS';
    protected $name        = 'auto-publish:run';
    protected $description = 'Menjalankan auto-publish postingan media sosial yang telah mencapai jadwal tayang (scheduled_at <= NOW).';

    public function run(array $params)
    {
        date_default_timezone_set('Asia/Jakarta');
        $nowStr = date('Y-m-d H:i:s');

        CLI::write("====================================================================", 'yellow');
        CLI::write("           SMMS AUTO-PUBLISH SCHEDULED BACKGROUND RUNNER            ", 'cyan');
        CLI::write("====================================================================", 'yellow');
        CLI::write("Waktu Server (WIB) : {$nowStr}", 'white');
        CLI::write("Memeriksa postingan jatuh tempo...\n", 'white');

        $service = new AutoPublishService();
        $startTime = microtime(true);

        try {
            $result = $service->processDuePosts();
            $duration = round(microtime(true) - $startTime, 2);

            if ($result['total'] === 0) {
                CLI::write("[INFO] Tidak ada postingan yang menunggu jadwal saat ini.", 'light_gray');
            } else {
                CLI::write("Ditemukan {$result['total']} postingan untuk diproses:\n", 'yellow');

                foreach ($result['detail'] as $item) {
                    $prefix = "[ID #{$item['id']}] {$item['judul']}";
                    if ($item['status'] === 'sukses') {
                        CLI::write(" ✔ {$prefix} -> {$item['pesan']}", 'green');
                    } elseif ($item['status'] === 'dilewati') {
                        CLI::write(" ⟳ {$prefix} -> {$item['pesan']}", 'yellow');
                    } else {
                        CLI::write(" ✖ {$prefix} -> {$item['pesan']}", 'red');
                    }
                }

                CLI::write("\n--- Ringkasan Eksekusi ---", 'cyan');
                CLI::write("Total Postingan : {$result['total']}", 'white');
                CLI::write("Berhasil        : {$result['sukses']}", 'green');
                CLI::write("Gagal           : {$result['gagal']}", 'red');
                CLI::write("Dilewati (Lock) : {$result['dilewati']}", 'yellow');
                CLI::write("Durasi Proses   : {$duration} detik\n", 'white');
            }
        } catch (\Throwable $e) {
            CLI::error("[FATAL ERROR] " . $e->getMessage());
            log_message('error', '[AutoPublishContent Command Error] ' . $e->getMessage());
        }

        CLI::write("Selesai.\n", 'yellow');
    }
}
