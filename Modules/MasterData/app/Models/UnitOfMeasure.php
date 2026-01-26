<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Traits\HasUnitScoping;

#[ObservedBy([\Modules\MasterData\Observers\MasterDataObserver::class])]
class UnitOfMeasure extends Model
{
    use HasFactory, HasUnitScoping;

    protected $table = 'units_of_measure';

    protected $fillable = ['unit_id', 'name', 'code'];

    protected static function newFactory(): \Modules\MasterData\Database\Factories\UnitOfMeasureFactory
    {
        return \Modules\MasterData\Database\Factories\UnitOfMeasureFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
