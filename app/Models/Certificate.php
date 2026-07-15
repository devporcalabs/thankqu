<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $table = 'certificates';

    protected $fillable = [
        'savings_plan_id',
        'certificate_number',
        'pdf_path',
        'revoked_at',
    ];

    protected $casts = [
        'revoked_at' => 'datetime',
    ];

    public function savingsPlan()
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }
}
