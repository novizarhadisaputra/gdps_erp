<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Unit extends Model
{
    use HasModuleSchema;
    use HasRoles, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'master_data.units';

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
