<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentLog extends Model
{
    protected $table = 'hiu_consent_logs';

    public $timestamps = false;

    protected $fillable = [
        'action',
        'consent_request_id',
        'consent_id',
        'patient_abha_address',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
