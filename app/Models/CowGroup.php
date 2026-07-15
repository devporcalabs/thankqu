<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CowGroup extends Model
{
    use HasFactory;

    protected $table = 'cow_groups';

    protected $fillable = [
        'code',
        'hijri_year',
        'filled_slots',
        'status', // open, full, ready, processed
    ];

    public function savingsPlans()
    {
        return $this->hasMany(SavingsPlan::class, 'cow_group_id');
    }

    public function locationVotes()
    {
        return $this->hasMany(GroupLocationVote::class, 'cow_group_id');
    }

    public function progressLogs()
    {
        return $this->hasMany(DistributionProgress::class, 'cow_group_id');
    }
}
