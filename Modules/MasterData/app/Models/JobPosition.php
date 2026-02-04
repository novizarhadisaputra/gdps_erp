<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'basic_salary',
        'risk_level',
        'is_labor_intensive',
        'is_active',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'is_labor_intensive' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function remunerationComponents(): BelongsToMany
    {
        return $this->belongsToMany(RemunerationComponent::class, 'job_position_remunerations')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function jobPositionRemunerations(): HasMany
    {
        return $this->hasMany(JobPositionRemuneration::class);
    }
}
