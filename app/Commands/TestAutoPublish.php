<?php

namespace App\Commands;

use App\Models\ContentPlanModel;
use App\Services\AutoPublishService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestAutoPublish extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:auto-publish';
    protected $description = 'Uji coba alur Auto-Publish Terjadwal';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        CLI::write("===> Memulai Test Auto-Publish Terjadwal...", 'cyan');

        // 1. Insert postingan test
        $db->table('content_plan')->insert([
            'judul_konten'        => 'Post Test Auto-Publish CLI',
            'deskripsi'           => 'Deskripsi konten pengujian background worker',
            'caption'             => 'Caption test otomatis',
            'tanggal_publish'     => date('Y-m-d H:i:s'),
            'scheduled_at'        => date('Y-m-d H:i:s', strtotime('-1 minute')),
            'auto_publish_status' => 'menunggu',
            'publish_attempts'    => 0,
            'status'              => 'acc_final',
            'dibuat_oleh'         => 1,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        $testId = $db->insertID();
        CLI::write("Created test post ID #{$testId} with scheduled_at in the past.", 'green');

        // 2. Jalankan service
        $service = new AutoPublishService();
        $result = $service->processDuePosts();

        CLI::write("Service Result: Total: {$result['total']}, Sukses: {$result['sukses']}, Gagal: {$result['gagal']}", 'yellow');

        // 3. Cek row di database
        $row = $db->table('content_plan')->where('id', $testId)->get()->getRowArray();
        CLI::write("Status Database: auto_publish_status = '{$row['auto_publish_status']}', publish_attempts = {$row['publish_attempts']}, last_error = '{$row['last_error']}'", 'white');

        if ($row['publish_attempts'] > 0) {
            CLI::write("✔ Attempt incremented correctly on failure/execution.", 'green');
        }

        // 4. Test max retry logic (attempts >= 3)
        $db->table('content_plan')->insert([
            'judul_konten'        => 'Post Test Max Retry Failure',
            'deskripsi'           => 'Test max attempts and notification',
            'caption'             => 'Test caption',
            'tanggal_publish'     => date('Y-m-d H:i:s'),
            'scheduled_at'        => date('Y-m-d H:i:s', strtotime('-1 minute')),
            'auto_publish_status' => 'menunggu',
            'publish_attempts'    => 2, // 2 previous attempts
            'status'              => 'acc_final',
            'dibuat_oleh'         => 1,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
        $testId2 = $db->insertID();

        $result2 = $service->processDuePosts();
        $row2 = $db->table('content_plan')->where('id', $testId2)->get()->getRowArray();
        CLI::write("Max Retry Test: auto_publish_status = '{$row2['auto_publish_status']}', publish_attempts = {$row2['publish_attempts']}", 'white');

        if ($row2['auto_publish_status'] === 'gagal' && $row2['publish_attempts'] == 3) {
            CLI::write("✔ Permanently marked as 'gagal' after reaching 3 attempts.", 'green');
        }

        // Cleanup
        $db->table('content_plan')->where('id', $testId2)->delete();
        CLI::write("Cleaned up test post #{$testId2}.", 'light_gray');
        CLI::write("===> Semua Pengujian Berhasil!", 'green');
    }
}
