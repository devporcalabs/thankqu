<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimalPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'animal_packages';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'category', // qurban, aqiqah
        'type', // domba, kambing, sapi_utuh, sapi_patungan
        'name',
        'price',
        'desc',
        'weight',
        'age',
        'fit',
        'image',
        'total_slots',
        'bundle_quantity',
        'is_active',
    ];

    public function savingsPlans()
    {
        return $this->hasMany(SavingsPlan::class, 'package_id');
    }
}
