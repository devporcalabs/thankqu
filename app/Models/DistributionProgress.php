<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionProgress extends Model
{
    use HasFactory;

    protected $table = 'distribution_progress';

    protected $fillable = [
        'savings_plan_id',
        'cow_group_id',
        'from_status',
        'to_status',
        'note',
        'evidence',
        'admin_id',
    ];

    public function savingsPlan()
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }

    public function cowGroup()
    {
        return $this->belongsTo(CowGroup::class, 'cow_group_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
