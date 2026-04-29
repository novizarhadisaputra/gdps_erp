<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Observers\JobPositionObserver;
use Modules\MasterData\Traits\HasDefaultRecord;

#[ObservedBy(JobPositionObserver::class)]
class JobPosition extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected static function newFactory()
    {
        return \Modules\MasterData\Database\Factories\JobPositionFactory::new();
    }

    protected $fillable = [
        'code',
        'name',
        'risk_level',
        'is_labor_intensive',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_labor_intensive' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function fixedAllowances(): HasMany
    {
        return $this->hasMany(JobPositionFixedAllowance::class);
    }

    public function nonFixedAllowances(): HasMany
    {
        return $this->hasMany(JobPositionNonFixedAllowance::class);
    }
}
