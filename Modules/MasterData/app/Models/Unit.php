<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Unit extends Model
{
    use HasFactory, HasRoles, HasUuids;
    use HasModuleSchema;

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
    ];

    public function superiorUnit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class, 'superior_unit');
    }
}
