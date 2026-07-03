<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    protected $fillable = [
        'patient_abha_address',
        'observation_date',
        'code',
        'display',
        'value',
        'unit',
        'status',
        'facility_name',
    ];

    protected $casts = [
        'observation_date' => 'datetime',
    ];
}
