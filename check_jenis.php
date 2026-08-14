<?php
$pdo = new PDO('mysql:host=localhost;dbname=smms_absys', 'root', '');
$res = $pdo->query('SELECT * FROM jenis_konten')->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
