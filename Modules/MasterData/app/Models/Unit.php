<?php

namespace Modules\MasterData\Models;

use App\Traits\HasDefaultRecord;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Unit extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasRoles, HasUuids;

    protected string $guard_name = 'web';

    protected static function newFactory(): \Modules\MasterData\Database\Factories\UnitFactory
    {
        return \Modules\MasterData\Database\Factories\UnitFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'units';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'external_id',
        'code',
        'name',
        'superior_unit',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function superiorUnit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class, 'superior_unit');
    }
}
