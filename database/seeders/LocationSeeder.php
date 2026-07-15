<?php

namespace Database\Seeders;

use App\Models\DistributionLocation;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DistributionLocation::query()->delete();

        DistributionLocation::create([
            'code' => 'LOC-PLS',
            'name' => 'Palestina (Lembaga Penyalur)',
            'category' => 'qurban',
            'region' => 'Palestina',
            'description' => 'Penyaluran khusus berupa paket daging qurban beku/tin untuk saudara-saudara kita di Jalur Gaza.',
            'quota' => 500,
            'capacity' => 500,
            'used_quota' => 0,
            'latitude' => 31.95220000,
            'longitude' => 35.23320000,
        ]);

        DistributionLocation::create([
            'code' => 'LOC-NTT',
            'name' => 'NTT - Waikabubak',
            'category' => 'qurban',
            'region' => 'Nusa Tenggara Timur',
            'description' => 'Penyaluran qurban di wilayah pelosok Waikabubak dengan tingkat stunting tinggi dan minoritas muslim.',
            'quota' => 200,
            'capacity' => 200,
            'used_quota' => 0,
            'latitude' => -9.64160000,
            'longitude' => 119.41240000,
        ]);

        DistributionLocation::create([
            'code' => 'LOC-PPB',
            'name' => 'Papua Barat - Sorong',
            'category' => 'qurban',
            'region' => 'Papua Barat',
            'description' => 'Penyaluran qurban kepada jamaah mualaf di pelosok Sorong dan pulau-pulau sekitarnya.',
            'quota' => 150,
            'capacity' => 150,
            'used_quota' => 0,
            'latitude' => -0.87530000,
            'longitude' => 131.25200000,
        ]);

        DistributionLocation::create([
            'code' => 'LOC-AQI',
            'name' => 'Rumah Aqiqah Sukabumi',
            'category' => 'aqiqah',
            'region' => 'Jawa Barat',
            'description' => 'Pusat penyembelihan dan pengolahan aqiqah terpadu untuk anak yatim piatu pra-sejahtera.',
            'quota' => 300,
            'capacity' => 300,
            'used_quota' => 0,
            'latitude' => -6.91810000,
            'longitude' => 106.92660000,
        ]);
    }
}
