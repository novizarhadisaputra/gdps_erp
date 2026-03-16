<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Database\Factories\UnitOfMeasureFactory;

class UnitOfMeasure extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected $table = 'units_of_measure';

    protected $fillable = ['name', 'code'];

    protected static function newFactory(): UnitOfMeasureFactory
    {
        return UnitOfMeasureFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
