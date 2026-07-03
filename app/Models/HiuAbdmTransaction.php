<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HiuAbdmTransaction extends Model
{
    protected $table = 'hiu_abdm_transactions';

    protected $fillable = [
        'transaction_id',
        'consent_id',
        'type',
        'status',
        'records_count',
        'error',
    ];
}
