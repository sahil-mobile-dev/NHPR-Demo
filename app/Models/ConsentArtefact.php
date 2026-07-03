<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentArtefact extends Model
{
    protected $fillable = [
        'consent_id',
        'status',
        'patient_abha_address',
        'consent_detail',
    ];

    protected $casts = [
        'consent_detail' => 'array',
    ];
}
