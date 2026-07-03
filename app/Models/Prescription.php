<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = [
        'patient_abha_address',
        'prescription_date',
        'doctor_name',
        'doctor_hpr_id',
        'medications',
        'facility_name',
    ];

    protected $casts = [
        'prescription_date' => 'datetime',
        'medications' => 'array',
    ];
}
