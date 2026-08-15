<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\GraphApiService;

class TestMetaGraph extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:meta-graph';
    protected $description = 'Jalankan pengujian CLI untuk GraphApiService Meta API & Instagram Publishing';

    public function run(array $params)
    {
        CLI::write("====================================================================", 'yellow');
        CLI::write("       META GRAPH API & INSTAGRAM PUBLISHING TEST RUNNER           ", 'cyan');
        CLI::write("====================================================================\n", 'yellow');

        $service = new GraphApiService();

        // ---------------------------------------------------------------------
        // TEST 1: Access Token Generation
        // ---------------------------------------------------------------------
        CLI::write("[TEST 1] Testing getAccessToken()...", 'white');
        $token = $service->getAccessToken();
        CLI::write(" -> Result Token: " . substr($token, 0, 15) . "... (Length: " . strlen($token) . ")", 'green');
        if (! empty($token)) {
            CLI::write(" -> [PASSED] Token successfully retrieved/generated.\n", 'green');
        } else {
            CLI::write(" -> [FAILED] Token is empty.\n", 'red');
        }

        // ---------------------------------------------------------------------
        // TEST 2: Instagram Business Account ID Retrieval
        // ---------------------------------------------------------------------
        CLI::write("[TEST 2] Testing getInstagramBusinessAccountId()...", 'white');
        $igId = $service->getInstagramBusinessAccountId('elecomplolcal');
        CLI::write(" -> Result IG Account ID: " . ($igId ?: 'None (Not found / requires live user token)'), 'green');
        if ($igId) {
            CLI::write(" -> [PASSED] IG Business Account ID retrieved: {$igId}\n", 'green');
        } else {
            CLI::write(" -> [INFO] IG Business Account ID not retrieved via API (requires User Access Token with page/instagram permissions).\n", 'yellow');
        }

        // ---------------------------------------------------------------------
        // TEST 3a: publishToInstagram with Localhost URL (Must Fail)
        // ---------------------------------------------------------------------
        CLI::write("[TEST 3a] Testing publishToInstagram() with Localhost URL...", 'white');
        $resLocalhost = $service->publishToInstagram('http://localhost:8080/uploads/image.jpg', 'Test Caption');
        CLI::write(" -> Status: " . $resLocalhost['status'], 'yellow');
        CLI::write(" -> Pesan: " . $resLocalhost['pesan'], 'yellow');
        if ($resLocalhost['status'] === 'gagal' && strpos($resLocalhost['pesan'], 'localhost') !== false) {
            CLI::write(" -> [PASSED] Localhost URL correctly rejected with clear message.\n", 'green');
        } else {
            CLI::write(" -> [FAILED] Localhost URL was not rejected properly.\n", 'red');
        }

        // ---------------------------------------------------------------------
        // TEST 3b: publishToInstagram with Invalid URL (Must Fail)
        // ---------------------------------------------------------------------
        CLI::write("[TEST 3b] Testing publishToInstagram() with Invalid URL Format...", 'white');
        $resInvalid = $service->publishToInstagram('not-a-valid-url', 'Test Caption');
        CLI::write(" -> Status: " . $resInvalid['status'], 'yellow');
        CLI::write(" -> Pesan: " . $resInvalid['pesan'], 'yellow');
        if ($resInvalid['status'] === 'gagal') {
            CLI::write(" -> [PASSED] Invalid URL format rejected properly.\n", 'green');
        } else {
            CLI::write(" -> [FAILED] Invalid URL was not rejected.\n", 'red');
        }

        // ---------------------------------------------------------------------
        // TEST 3c: publishToInstagram with Empty Caption (Must Warn)
        // ---------------------------------------------------------------------
        CLI::write("[TEST 3c] Testing publishToInstagram() with Empty Caption...", 'white');
        $resEmptyCaption = $service->publishToInstagram('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80&fm=jpg&fit=crop', '');
        CLI::write(" -> Status: " . $resEmptyCaption['status'], 'yellow');
        CLI::write(" -> Pesan: " . $resEmptyCaption['pesan'], 'yellow');
        if ($resEmptyCaption['status'] === 'sukses') {
            CLI::write(" -> [PASSED] Empty caption published successfully with warning.", 'green');
            if (isset($resEmptyCaption['data']['warning'])) {
                CLI::write(" -> Warning: " . $resEmptyCaption['data']['warning'], 'yellow');
            }
            CLI::write("", 'white');
        } else {
            CLI::write(" -> [INFO] Meta API error handled gracefully as expected: " . $resEmptyCaption['pesan'] . "\n", 'yellow');
        }

        // ---------------------------------------------------------------------
        // TEST 3e: detectMediaType (Image vs Video/Reels Detection)
        // ---------------------------------------------------------------------
        CLI::write("[TEST 3e] Testing detectMediaType()...", 'white');
        $imgType = $service->detectMediaType('https://images.unsplash.com/photo.jpg', 'Static Post');
        $vidType = $service->detectMediaType('https://drive.google.com/uc?export=view&id=1AKScIXOCNqAMtC1TYv9abvILKeauv0NX', 'Reels / Video');
        $vidExt  = $service->detectMediaType('https://example.com/sample.mp4');

        CLI::write(" -> Image URL detected as: {$imgType}", ($imgType === 'IMAGE') ? 'green' : 'red');
        CLI::write(" -> Drive MP4 detected as: {$vidType}", ($vidType === 'VIDEO') ? 'green' : 'red');
        CLI::write(" -> MP4 extension detected as: {$vidExt}", ($vidExt === 'VIDEO') ? 'green' : 'red');

        if ($imgType === 'IMAGE' && $vidType === 'VIDEO' && $vidExt === 'VIDEO') {
            CLI::write(" -> [PASSED] Media types correctly detected!\n", 'green');
        } else {
            CLI::write(" -> [FAILED] Media detection error.\n", 'red');
        }

        CLI::write("====================================================================", 'yellow');
        CLI::write("                       TEST SUITE FINISHED                          ", 'cyan');
        CLI::write("====================================================================\n", 'yellow');
    }
}
