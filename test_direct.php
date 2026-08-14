<?php
define('FCPATH', __DIR__ . '/public/');
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

$graphService = new \App\Services\GraphApiService();

echo "=== TESTING IMAGE STORY ===\n";
$imgUrl = 'https://storage.googleapis.com/gtv-videos-bucket/sample/images/BigBuckBunny.jpg';
$resImg = $graphService->publishStoryToInstagram($imgUrl, 'Test image story via API');
print_r($resImg);

echo "\n=== TESTING VIDEO STORY ===\n";
$vidUrl = 'https://storage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4';
$resVid = $graphService->publishStoryToInstagram($vidUrl, 'Test video story via API');
print_r($resVid);
