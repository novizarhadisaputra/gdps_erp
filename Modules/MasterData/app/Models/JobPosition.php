<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosition extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected static function newFactory()
    {
        return \Modules\MasterData\Database\Factories\JobPositionFactory::new();
    }

    protected $fillable = [
        'name',
        'risk_level',
        'is_labor_intensive',
        'is_active',
    ];

    protected $casts = [
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
