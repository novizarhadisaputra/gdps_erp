<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Database\Factories\UnitOfMeasureFactory;
use Modules\MasterData\Traits\HasUnitScoping;

class UnitOfMeasure extends Model
{
    use HasFactory, HasUnitScoping, HasUuids;
    use HasModuleSchema;

    protected $table = 'units_of_measure';

    protected $fillable = ['unit_id', 'name', 'code'];

    protected static function newFactory(): UnitOfMeasureFactory
    {
        return UnitOfMeasureFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
