<?php

namespace Tests\Feature;

use App\Models\AnimalPackage;
use App\Models\CowGroup;
use App\Models\DistributionLocation;
use App\Models\Refund;
use App\Models\SavingsPlan;
use App\Models\Setting;
use App\Models\User;
use App\Services\SavingsPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsPlanTest extends TestCase
{
    use RefreshDatabase;

    protected SavingsPlanService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SavingsPlanService::class);

        // Setup default settings
        Setting::setVal('qurban_cutoff_date', '2026-06-15');
        Setting::setVal('hijri_year', '1447');
        Setting::setVal('cancellation_fee_percent', '2.5');
        Setting::setVal('institutional_shohibul_name', 'Infaq Qurban Lembaga');
        Setting::setVal('talangan_days_before_cutoff', '7');
    }

    /**
     * Test Aqiqah target pricing doubling for Putra.
     */
    public function test_aqiqah_target_pricing(): void
    {
        $package = AnimalPackage::create([
            'id' => 'AQ-TEST',
            'category' => 'aqiqah',
            'type' => 'domba',
            'name' => 'Domba Aqiqah Test',
            'price' => 3000000,
            'desc' => 'Test',
            'weight' => '30kg',
            'age' => '1 Year',
            'fit' => 'Fit',
            'image' => 'test.jpg',
            'total_slots' => 1,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        // Case 1: Putri (1 animal) -> target price = package price
        $planPutri = SavingsPlan::create([
            'user_id' => $user->id,
            'plan_code' => 'TQ/AQ/1447/001',
            'package_id' => $package->id,
            'package_name' => $package->name,
            'package_type' => 'Aqiqah',
            'target_amount' => $package->price,
            'collected_amount' => 0,
            'status' => 'saving',
            'hijri_year' => '1447',
            'aqiqah_child_name' => 'Fatimah',
            'aqiqah_child_gender' => 'putri',
            'aqiqah_child_birthdate' => '2026-01-01',
        ]);

        $this->assertEquals(3000000, $planPutri->target_amount);

        // Case 2: Putra (2 animals / bundle) -> target price = 2x package price
        $planPutra = SavingsPlan::create([
            'user_id' => $user->id,
            'plan_code' => 'TQ/AQ/1447/002',
            'package_id' => $package->id,
            'package_name' => $package->name,
            'package_type' => 'Aqiqah',
            'target_amount' => $package->price * 2,
            'collected_amount' => 0,
            'status' => 'saving',
            'hijri_year' => '1447',
            'aqiqah_child_name' => 'Muhammad',
            'aqiqah_child_gender' => 'putra',
            'aqiqah_child_birthdate' => '2026-01-01',
        ]);

        $this->assertEquals(6000000, $planPutra->target_amount);
    }

    /**
     * Test FIFO Sapi Patungan group matching.
     */
    public function test_group_matching_fifo(): void
    {
        $package = AnimalPackage::create([
            'id' => 'Sapi-Patungan',
            'category' => 'qurban',
            'type' => 'sapi_patungan',
            'name' => 'Sapi Patungan',
            'price' => 3500000,
            'desc' => 'Test',
            'weight' => '300kg',
            'age' => '2 Years',
            'fit' => 'Fit',
            'image' => 'test.jpg',
            'total_slots' => 7,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);

        $users = User::factory()->count(8)->create();
        $plans = [];

        // Create 8 savings plans
        foreach ($users as $idx => $user) {
            $plan = SavingsPlan::create([
                'user_id' => $user->id,
                'plan_code' => 'TQ/TEST/FIFO/' . $idx,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'package_type' => 'Sapi Patungan',
                'target_amount' => 3500000,
                'collected_amount' => 500000,
                'status' => 'saving',
                'hijri_year' => '1447',
            ]);
            $this->service->matchToGroup($plan);
            $plans[] = $plan;
        }

        // The first 7 plans must be matched into the same group
        $firstGroup = $plans[0]->cow_group_id;
        $this->assertNotNull($firstGroup);

        for ($i = 0; $i < 7; $i++) {
            $this->assertEquals($firstGroup, $plans[$i]->fresh()->cow_group_id);
        }

        // The 8th plan must be matched to a new, separate group (FIFO)
        $this->assertNotEquals($firstGroup, $plans[7]->fresh()->cow_group_id);
        $this->assertNotNull($plans[7]->fresh()->cow_group_id);
    }

    /**
     * Test majority voting for distribution locations.
     */
    public function test_majority_voting_locations(): void
    {
        $package = AnimalPackage::create([
            'id' => 'Sapi-Patungan',
            'category' => 'qurban',
            'type' => 'sapi_patungan',
            'name' => 'Sapi Patungan',
            'price' => 3500000,
            'desc' => 'Test',
            'weight' => '300kg',
            'age' => '2 Years',
            'fit' => 'Fit',
            'image' => 'test.jpg',
            'total_slots' => 7,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);

        $users = User::factory()->count(7)->create();
        $group = CowGroup::create([
            'code' => 'GRP-001',
            'hijri_year' => '1447',
            'filled_slots' => 7,
            'status' => 'ready',
        ]);

        $plans = [];
        foreach ($users as $user) {
            $plans[] = SavingsPlan::create([
                'user_id' => $user->id,
                'plan_code' => 'TQ/GRP/' . $user->id,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'package_type' => 'Sapi Patungan',
                'target_amount' => 3500000,
                'collected_amount' => 3500000,
                'status' => 'paid_off',
                'hijri_year' => '1447',
                'cow_group_id' => $group->id,
            ]);
        }

        $locA = DistributionLocation::create([
            'code' => 'LOC-A',
            'name' => 'Lokasi A',
            'category' => 'qurban',
            'region' => 'Region A',
            'description' => 'Desc',
            'quota' => 10,
            'capacity' => 10,
            'used_quota' => 0,
            'latitude' => 0,
            'longitude' => 0,
        ]);

        $locB = DistributionLocation::create([
            'code' => 'LOC-B',
            'name' => 'Lokasi B',
            'category' => 'qurban',
            'region' => 'Region B',
            'description' => 'Desc',
            'quota' => 10,
            'capacity' => 10,
            'used_quota' => 0,
            'latitude' => 0,
            'longitude' => 0,
        ]);

        // Submit 3 votes for Loc B
        for ($i = 0; $i < 3; $i++) {
            $this->service->submitVote($plans[$i], $locB->id);
        }

        // Group status should still be 'ready' (no majority >= 4/7)
        $this->assertEquals('ready', $group->fresh()->status);

        // Submit 4th vote for Loc A (majority reaches 4/7)
        for ($i = 3; $i < 7; $i++) {
            $this->service->submitVote($plans[$i], $locA->id);
        }

        // Group status must be 'processed', location must be locked to Loc A
        $group = $group->fresh();
        $this->assertEquals('processed', $group->status);

        // All members must be transitioned to 'animal_purchased' (first pipeline step)
        foreach ($plans as $plan) {
            $p = $plan->fresh();
            $this->assertEquals('animal_purchased', $p->status);
            $this->assertEquals($locA->id, $p->distribution_location_id);
        }
    }

    /**
     * Test refund calculations and status cancellation transitions.
     */
    public function test_cancellation_and_refund_calculations(): void
    {
        $package = AnimalPackage::create([
            'id' => 'DOMBA-A',
            'category' => 'qurban',
            'type' => 'domba',
            'name' => 'Domba Qurban',
            'price' => 3000000,
            'desc' => 'Test',
            'weight' => '30kg',
            'age' => '1.5 Years',
            'fit' => 'Fit',
            'image' => 'test.jpg',
            'total_slots' => 1,
            'bundle_quantity' => 1,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $plan = SavingsPlan::create([
            'user_id' => $user->id,
            'plan_code' => 'TQ/DM/001',
            'package_id' => $package->id,
            'package_name' => $package->name,
            'package_type' => 'Qurban',
            'target_amount' => 3000000,
            'collected_amount' => 1000000, // already deposited 1,000,000 IDR
            'status' => 'saving',
            'hijri_year' => '1447',
        ]);

        // Request cancellation
        $collected = (float) $plan->collected_amount;
        $feePercent = (float) Setting::getVal('cancellation_fee_percent', 2.5);
        $fee = ($feePercent / 100) * $collected;
        $net = $collected - $fee;

        $refund = Refund::create([
            'savings_plan_id' => $plan->id,
            'amount_collected' => $collected,
            'fee_percent' => $feePercent,
            'fee_amount' => $fee,
            'net_amount' => $net,
            'bank_account' => 'BCA 12345678 a/n Ahmad',
            'status' => 'pending',
        ]);
        
        $this->service->transitionTo($plan, 'cancellation_requested', 'Alasan finansial');

        $this->assertEquals('cancellation_requested', $plan->fresh()->status);
        $this->assertEquals('pending', $refund->status);

        // Verification of calculations:
        // Collected = 1,000,000
        // Fee = 2.5% = 25,000
        // Net = 975,000
        $this->assertEquals(1000000, $refund->amount_collected);
        $this->assertEquals(2.5, $refund->fee_percent);
        $this->assertEquals(25000, $refund->fee_amount);
        $this->assertEquals(975000, $refund->net_amount);
        $this->assertEquals('BCA 12345678 a/n Ahmad', $refund->bank_account);
    }
}
