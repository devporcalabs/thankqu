<?php

namespace Database\Seeders;

use App\Models\CowGroup;
use App\Models\SavingsPlan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class SavingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SavingsPlan::query()->delete();
        CowGroup::query()->delete();
        Transaction::query()->delete();

        $ahmad = User::where('email', 'ahmad@thankquu.com')->first();
        if ($ahmad) {
            // Create a group for patungan
            $group = CowGroup::create([
                'code' => 'GRP-1447-001',
                'hijri_year' => 1447,
                'filled_slots' => 1,
                'status' => 'open',
            ]);

            $plan = SavingsPlan::create([
                'plan_code' => 'TQ-PLAN-SEED1',
                'user_id' => $ahmad->id,
                'package_id' => 'Sapi-Patungan',
                'package_name' => 'Sapi Patungan (1/7 Kelompok)',
                'package_type' => 'Sapi Patungan',
                'locked_price' => 3500000.00,
                'target_amount' => 3500000.00,
                'collected_amount' => 2300000.00,
                'remaining_amount' => 1200000.00,
                'progress_percent' => 66,
                'status' => 'saving',
                'hijri_year' => 1447,
                'shohibul_name' => 'Ahmad Fauzan',
                'cow_group_id' => $group->id,
            ]);

            Transaction::create([
                'savings_plan_id' => $plan->id,
                'order_id' => 'TQ-TX-SEED-1',
                'type' => 'Top Up Saldo',
                'amount' => 500000.00,
                'date' => '20 May 2026',
                'time' => '14:20 WIB',
                'status' => 'Success',
                'channel' => 'Virtual Account',
                'payment_method' => 'Virtual Account',
                'paid_at' => now()->subDays(30),
            ]);

            Transaction::create([
                'savings_plan_id' => $plan->id,
                'order_id' => 'TQ-TX-SEED-2',
                'type' => 'Top Up Saldo',
                'amount' => 1000000.00,
                'date' => '25 April 2026',
                'time' => '08:00 WIB',
                'status' => 'Success',
                'channel' => 'E-Wallet',
                'payment_method' => 'E-Wallet',
                'paid_at' => now()->subDays(50),
            ]);

            Transaction::create([
                'savings_plan_id' => $plan->id,
                'order_id' => 'TQ-TX-SEED-3',
                'type' => 'Top Up Saldo',
                'amount' => 800000.00,
                'date' => '12 April 2026',
                'time' => '10:45 WIB',
                'status' => 'Success',
                'channel' => 'Virtual Account',
                'payment_method' => 'Virtual Account',
                'paid_at' => now()->subDays(60),
            ]);
        }
    }
}
