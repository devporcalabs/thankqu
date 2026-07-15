<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingsPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'savings_plans';

    protected $fillable = [
        'plan_code',
        'user_id',
        'package_id',
        'package_name',
        'package_type', // Maps category (Domba, Sapi, etc.)
        'locked_price',
        'target_amount',
        'collected_amount',
        'remaining_amount',
        'progress_percent',
        'next_payment_deadline',
        'status', // saving, paid_off, distribution_selected, animal_purchased, slaughtered, report_done, completed, cancelled, carried_over
        'hijri_year',
        'is_institutional',
        'shohibul_name',
        'aqiqah_child_name',
        'aqiqah_child_gender',
        'aqiqah_child_birthdate',
        'requested_execution_date',
        'scheduled_execution_date',
        'repriced_at',
        'cow_group_id',
        'distribution_location_id',
    ];

    protected $casts = [
        'is_institutional' => 'boolean',
        'aqiqah_child_birthdate' => 'date',
        'requested_execution_date' => 'date',
        'scheduled_execution_date' => 'date',
        'repriced_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($plan) {
            $plan->remaining_amount = max(0, $plan->target_amount - $plan->collected_amount);
            $plan->progress_percent = $plan->target_amount > 0 ? min(100, round(($plan->collected_amount / $plan->target_amount) * 100, 2)) : 0;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(AnimalPackage::class, 'package_id');
    }

    public function cowGroup()
    {
        return $this->belongsTo(CowGroup::class, 'cow_group_id');
    }

    public function location()
    {
        return $this->belongsTo(DistributionLocation::class, 'distribution_location_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'savings_plan_id');
    }

    public function refund()
    {
        return $this->hasOne(Refund::class, 'savings_plan_id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'savings_plan_id');
    }

    public function progressLogs()
    {
        return $this->hasMany(DistributionProgress::class, 'savings_plan_id');
    }

    /**
     * Recalculate and update the remaining amount and progress percentage.
     */
    public function recalculateProgress()
    {
        $collected = (float) $this->collected_amount;
        $target = (float) $this->target_amount;

        $remaining = max(0.0, $target - $collected);
        $progress = $target > 0 ? (int) round(($collected / $target) * 100) : 0;

        $this->update([
            'remaining_amount' => $remaining,
            'progress_percent' => $progress,
        ]);
    }
}
