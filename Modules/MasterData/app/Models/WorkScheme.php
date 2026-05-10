<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\WorkSchemeFactory;
use Modules\MasterData\Observers\WorkSchemeObserver;
use Modules\MasterData\Traits\HasDefaultRecord;

#[ObservedBy(WorkSchemeObserver::class)]
class WorkScheme extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'working_days',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'working_days' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): WorkSchemeFactory
    {
        return WorkSchemeFactory::new();
    }
}
