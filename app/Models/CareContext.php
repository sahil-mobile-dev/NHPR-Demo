<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareContext extends Model
{
    protected $fillable = [
        'patient_abha_number',
        'patient_abha_address',
        'care_context_reference',
        'display',
        'is_linked',
    ];

    protected $casts = [
        'is_linked' => 'boolean',
    ];

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }
}
