<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $table = 'refunds';

    protected $fillable = [
        'savings_plan_id',
        'amount_collected',
        'fee_percent',
        'fee_amount',
        'net_amount',
        'bank_account',
        'proof_path',
        'status', // pending, approved, rejected
    ];

    public function savingsPlan()
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }
}
