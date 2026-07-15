<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupLocationVote extends Model
{
    use HasFactory;

    protected $table = 'group_location_votes';

    protected $fillable = [
        'cow_group_id',
        'savings_plan_id',
        'distribution_location_id',
        'voted_at',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    public function cowGroup()
    {
        return $this->belongsTo(CowGroup::class, 'cow_group_id');
    }

    public function savingsPlan()
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }

    public function location()
    {
        return $this->belongsTo(DistributionLocation::class, 'distribution_location_id');
    }
}
