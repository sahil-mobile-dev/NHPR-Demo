<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HiuTransaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'consent_id',
        'status',
        'private_key',
        'public_key',
        'nonce',
    ];
}
