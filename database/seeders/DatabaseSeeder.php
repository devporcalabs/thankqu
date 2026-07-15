<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LivestockSeeder::class,
            LocationSeeder::class,
            SavingsSeeder::class,
        ]);

        // Seed default application settings
        Setting::setVal('qurban_cutoff_date', '2026-06-15');
        Setting::setVal('hijri_year', '1447');
        Setting::setVal('cancellation_fee_percent', '2.5');
        Setting::setVal('institutional_shohibul_name', 'Infaq Qurban Lembaga');
        Setting::setVal('talangan_days_before_cutoff', '7');
    }
}
