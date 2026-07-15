<?php

namespace Database\Seeders;

use App\Models\AnimalPackage;
use Illuminate\Database\Seeder;

class LivestockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing packages
        AnimalPackage::query()->delete();

        // 1. Qurban Packages
        AnimalPackage::create([
            'id' => 'Domba-A',
            'category' => 'qurban',
            'type' => 'domba',
            'name' => 'Domba Qurban Standar',
            'price' => 2300000.00,
            'desc' => 'Domba sehat dengan kualitas prima untuk ibadah qurban berkah.',
            'weight' => '23-26 kg',
            'age' => '1.2 Tahun',
            'fit' => 'Fit (Sehat)',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD01xxU7e937WQxR4nYZpcjwo3jrOsDL0hZredv170fV4494Sf4nk9mK9lA9C5la2mN7465F5kGeB0lfun46gDzvLGzgc3r_YJEc-TmkqzjKsC1VBRyTzO-ox9KmtD3xD8cU3bhz-mebaqpf7gnkhDuTirVNvOQnawacxh1Ibe7Xn3txcLOnO6fNSn8pIlhEStA1M4DefT4WQxQ_G_IBqrPm0z8RLaQx2cb2bSc-mvnDyF56DNfk0P86tyTWqwOjFRWjeZ88x6bGAoE',
            'total_slots' => 1,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);

        AnimalPackage::create([
            'id' => 'Kambing-A',
            'category' => 'qurban',
            'type' => 'kambing',
            'name' => 'Kambing Qurban Premium',
            'price' => 2700000.00,
            'desc' => 'Kambing gemuk dengan nutrisi terjamin, cocok untuk keluarga.',
            'weight' => '28-32 kg',
            'age' => '1.5 Tahun',
            'fit' => 'Fit (Sehat)',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAcSKoiNDvNcj7WRqA30TTDZ62jeDCtrrc51auT7bX9yL34Fh_OCm6WOVaYVJwyqMXDV_wBixrCvTKn8X9XtHY0n9zGfn4B-v-Hyg26rri-M52xuKRm7ZzVLBebahZV7Uvgyb4CFVDvS8jjUXvuS5k7CluJ_125V05rr_yWRX9ay0NoAzcs4vHiIyEPl_Qnnng7GtuIYZK6dYKw9iqNqFSlYvxrLktZEVrNW1nByzo1tcSZ1--Go092HFiwDmwKYQxtS9qoq6huwPwa',
            'total_slots' => 1,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);

        AnimalPackage::create([
            'id' => 'Sapi-Patungan',
            'category' => 'qurban',
            'type' => 'sapi_patungan',
            'name' => 'Sapi Patungan (1/7 Kelompok)',
            'price' => 3500000.00,
            'desc' => 'Patungan qurban sapi 1/7 kelompok. Pembagian kelompok diatur sistem otomatis syariah.',
            'weight' => '300-350 kg (total Sapi)',
            'age' => '2.5 Tahun',
            'fit' => 'Fit (Sehat)',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD_kA8C62RZ59pO-FPosUtY3y231uFZ-ZJ43nnjSnDRRcKH0Tdpt5W7chA1fcoIQVgP6q15dMZXWfSQjBOlxIUA-iYyb9iZGgLeAimE0OTUnANRw9O4UCgnbF4nRntL6WDlBQr80G6z3nH9j5x7zKpp28OouByRiLM5RXOjOQMx_pSoXZ4bqgDS4ryb2_LkFNZU27w6BFq5-e620LfwbVOUJxlt8RHkNBZpLvC7PADfsrCipWyajbmXmQfBwAZSehJKDT2AP2L_3TBM',
            'total_slots' => 7,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);

        AnimalPackage::create([
            'id' => 'Sapi-Utuh',
            'category' => 'qurban',
            'type' => 'sapi_utuh',
            'name' => 'Sapi Qurban 1 Ekor Utuh',
            'price' => 24000000.00,
            'desc' => 'Sapi Limousin utuh premium untuk qurban atas nama keluarga besar Anda (7 orang).',
            'weight' => '420-480 kg',
            'age' => '2.8 Tahun',
            'fit' => 'Fit (Sehat)',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDnGNcEb4t2Prwhfv2FLXutI-qb7nCCXFZkq-OXYSGIShiDll227LAvZfTcxENB1GUk1uyWzgklQ9Tfzcvk_lQYNR5TmSPIzw3lojNWIlq8qWMRG3qQqseg3JpIanIgRnZlDXPP5YZn-gy4eUkxtXqIMcNv1slowvpdQSNN4qKmGBKM92KX3HJ02YuoMGiDV7YuMqKJ3JYrzLRHi-opXIkTHO7I-_FhPYamarZw8nVwSHDDswfeq-Z1HLiioW5qFSOf41CIaFoev1ZO',
            'total_slots' => 1,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);

        // 2. Aqiqah Packages (Only domba/kambing, no sapi)
        AnimalPackage::create([
            'id' => 'Aqiqah-Domba',
            'category' => 'aqiqah',
            'type' => 'domba',
            'name' => 'Domba Aqiqah Standar',
            'price' => 2300000.00,
            'desc' => 'Domba aqiqah sehat pilihan, disembelih sesuai syariat.',
            'weight' => '23-26 kg',
            'age' => '1.2 Tahun',
            'fit' => 'Fit (Sehat)',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD01xxU7e937WQxR4nYZpcjwo3jrOsDL0hZredv170fV4494Sf4nk9mK9lA9C5la2mN7465F5kGeB0lfun46gDzvLGzgc3r_YJEc-TmkqzjKsC1VBRyTzO-ox9KmtD3xD8cU3bhz-mebaqpf7gnkhDuTirVNvOQnawacxh1Ibe7Xn3txcLOnO6fNSn8pIlhEStA1M4DefT4WQxQ_G_IBqrPm0z8RLaQx2cb2bSc-mvnDyF56DNfk0P86tyTWqwOjFRWjeZ88x6bGAoE',
            'total_slots' => 1,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);

        AnimalPackage::create([
            'id' => 'Aqiqah-Kambing',
            'category' => 'aqiqah',
            'type' => 'kambing',
            'name' => 'Kambing Aqiqah Premium',
            'price' => 2700000.00,
            'desc' => 'Kambing aqiqah premium gemuk, dirawat dengan kebersihan terjamin.',
            'weight' => '28-32 kg',
            'age' => '1.5 Tahun',
            'fit' => 'Fit (Sehat)',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAcSKoiNDvNcj7WRqA30TTDZ62jeDCtrrc51auT7bX9yL34Fh_OCm6WOVaYVJwyqMXDV_wBixrCvTKn8X9XtHY0n9zGfn4B-v-Hyg26rri-M52xuKRm7ZzVLBebahZV7Uvgyb4CFVDvS8jjUXvuS5k7CluJ_125V05rr_yWRX9ay0NoAzcs4vHiIyEPl_Qnnng7GtuIYZK6dYKw9iqNqFSlYvxrLktZEVrNW1nByzo1tcSZ1--Go092HFiwDmwKYQxtS9qoq6huwPwa',
            'total_slots' => 1,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);
    }
}
