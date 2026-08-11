<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder
 *
 * Entry point utama seeder — jalankan dengan:
 *   php spark db:seed DatabaseSeeder
 *
 * Urutan pemanggilan seeder penting karena ada FK dependency:
 * roles harus diisi sebelum users (agar default role bisa di-set).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(MasterDataSeeder::class);
        $this->call(ContentPlanSeeder::class);
        $this->call(TrendBankSeeder::class);
        $this->call(BrandAssetsSeeder::class);
    }
}
