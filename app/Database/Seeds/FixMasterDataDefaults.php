<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FixMasterDataDefaults extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();
        $db->query("UPDATE jenis_konten SET bisnis_id = NULL");
        $db->query("UPDATE platforms SET bisnis_id = NULL");
        $db->query("UPDATE content_types SET bisnis_id = NULL");
        echo "Master data defaults set to NULL (global fallback) successfully.\n";
    }
}
