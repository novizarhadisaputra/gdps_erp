<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Database\Factories\WorkSchemeFactory;
use Modules\MasterData\Traits\HasUnitScoping;

// use Modules\MasterData\Database\Factories\WorkSchemeFactory;

class WorkScheme extends Model
{
    use HasFactory, HasUnitScoping, HasUuids;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_id',
        'code',
        'name',
        'working_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'working_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): WorkSchemeFactory
    {
        return WorkSchemeFactory::new();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
