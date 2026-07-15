<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentNotification extends Model
{
    use HasFactory;

    protected $table = 'payment_notifications';

    protected $fillable = [
        'raw_payload',
        'signature_valid',
        'processed',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'signature_valid' => 'boolean',
        'processed' => 'boolean',
    ];
}
