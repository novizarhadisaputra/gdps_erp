<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Unit extends Model
{
    use HasRoles, HasUuids;

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
}
