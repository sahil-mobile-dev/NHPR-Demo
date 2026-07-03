<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthDocument extends Model
{
    protected $fillable = [
        'patient_abha_address',
        'document_type',
        'document_date',
        'title',
        'author_name',
        'facility_name',
        'file_content',
    ];

    protected $casts = [
        'document_date' => 'datetime',
    ];
}
