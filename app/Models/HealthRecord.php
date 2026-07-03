<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    protected $fillable = [
        'care_context_id',
        'record_type',
        'record_date',
        'fhir_data',
    ];

    protected $casts = [
        'record_date' => 'datetime',
        'fhir_data' => 'array',
    ];

    public function careContext(): BelongsTo
    {
        return $this->belongsTo(CareContext::class);
    }
}
