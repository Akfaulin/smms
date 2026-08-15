<?php

namespace App\Commands;

use App\Services\AutoPublishService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AutoPublishWatch extends BaseCommand
{
    protected $group       = 'SMMS';
    protected $name        = 'auto-publish:watch';
    protected $description = 'Menjalankan auto-publish background daemon secara terus menerus (looping tiap 30 detik) untuk keperluan testing lokal.';

    public function run(array $params)
    {
        date_default_timezone_set('Asia/Jakarta');
        $interval = 30; // detik

        CLI::write("====================================================================", 'yellow');
        CLI::write("       SMMS LOCAL AUTO-PUBLISH LIVE WORKER DAEMON (LOOP)            ", 'cyan');
        CLI::write("====================================================================", 'yellow');
        CLI::write("Worker aktif dan memeriksa jadwal postingan setiap {$interval} detik.", 'green');
        CLI::write("Tekan Ctrl + C di terminal untuk menghentikan worker.\n", 'light_gray');

        $service = new AutoPublishService();

        while (true) {
            $nowStr = date('Y-m-d H:i:s');
            CLI::write("[{$nowStr}] Memeriksa postingan jatuh tempo...", 'light_gray');

            try {
                $res = $service->processDuePosts();

                if ($res['total'] > 0) {
                    CLI::write(">>> Ditemukan {$res['total']} postingan: {$res['sukses']} sukses, {$res['gagal']} gagal.", 'cyan');
                    foreach ($res['detail'] as $item) {
                        $color = ($item['status'] === 'sukses') ? 'green' : (($item['status'] === 'dilewati') ? 'yellow' : 'red');
                        CLI::write("  - [ID #{$item['id']}] {$item['judul']} -> {$item['pesan']}", $color);
                    }
                }
            } catch (\Throwable $e) {
                CLI::error("[ERROR] " . $e->getMessage());
            }

            sleep($interval);
        }
    }
}
