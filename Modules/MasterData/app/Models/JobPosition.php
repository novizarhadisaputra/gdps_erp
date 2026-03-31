<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Observers\JobPositionObserver;

#[ObservedBy(JobPositionObserver::class)]
class JobPosition extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

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
    ];

    protected $casts = [
        'is_labor_intensive' => 'boolean',
        'is_active' => 'boolean',
    ];
}
