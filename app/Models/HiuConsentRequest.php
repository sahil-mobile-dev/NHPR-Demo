<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HiuConsentRequest extends Model
{
    protected $fillable = [
        'consent_request_id',
        'patient_abha_address',
        'status',
        'purpose',
        'hi_types',
        'date_from',
        'date_to',
        'expiry',
    ];

    protected $casts = [
        'hi_types' => 'array',
        'date_from' => 'datetime',
        'date_to' => 'datetime',
        'expiry' => 'datetime',
    ];
}
