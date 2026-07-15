<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'package_id',
    'package_name',
    'package_type',
    'target_amount',
    'current_amount',
    'remaining_amount',
    'progress_percent',
    'next_payment_deadline',
    'status',
    'penyaluran_method',
    'penyaluran_receiver',
    'penyaluran_phone',
    'penyaluran_address',
    'penyaluran_status',
    'cert_number',
])]
class Saving extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'savings_id');
    }
}
