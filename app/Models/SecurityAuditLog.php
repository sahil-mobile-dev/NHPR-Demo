<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAuditLog extends Model
{
    protected $fillable = [
        'transaction_id',
        'consent_id',
        'hiu_id',
        'patient_abha_address',
        'records_transferred',
        'status',
    ];
}
