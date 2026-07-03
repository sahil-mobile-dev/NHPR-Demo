<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthRecordLog extends Model
{
    protected $table = 'hiu_health_record_logs';

    public $timestamps = false;

    protected $fillable = [
        'patient_abha_address',
        'action',
        'details',
        'status',
    ];
}
