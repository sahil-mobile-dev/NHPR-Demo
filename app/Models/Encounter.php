<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encounter extends Model
{
    protected $fillable = [
        'patient_abha_address',
        'encounter_date',
        'encounter_type',
        'class_code',
        'status',
        'facility_name',
        'doctor_name',
    ];

    protected $casts = [
        'encounter_date' => 'datetime',
    ];
}
