<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'savings_plan_id',
    'order_id',
    'type',
    'amount',
    'date',
    'time',
    'status',
    'snap_token',
    'payment_method',
    'channel',
    'is_manual',
    'manual_note',
    'paid_at',
])]
class Transaction extends Model
{
    protected $casts = [
        'is_manual' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function savingPlan()
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }
}
