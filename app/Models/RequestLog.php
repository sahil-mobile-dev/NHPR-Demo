<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $table = 'hiu_request_logs';

    public $timestamps = false;

    protected $fillable = [
        'action',
        'transaction_id',
        'consent_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
