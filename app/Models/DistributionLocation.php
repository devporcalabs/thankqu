<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionLocation extends Model
{
    use HasFactory;

    protected $table = 'distribution_locations';

    protected $fillable = [
        'code',
        'name',
        'category', // qurban, aqiqah
        'region',
        'description',
        'quota',
        'capacity',
        'used_quota',
        'latitude',
        'longitude',
    ];

    public function savingsPlans()
    {
        return $this->hasMany(SavingsPlan::class, 'distribution_location_id');
    }
}
