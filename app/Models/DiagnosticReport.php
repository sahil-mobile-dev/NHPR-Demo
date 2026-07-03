<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticReport extends Model
{
    protected $fillable = [
        'patient_abha_address',
        'report_date',
        'test_name',
        'category',
        'result_status',
        'conclusion',
        'facility_name',
        'doctor_name',
    ];

    protected $casts = [
        'report_date' => 'datetime',
    ];
}
