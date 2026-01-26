<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Traits\HasUnitScoping;

#[ObservedBy([\Modules\MasterData\Observers\MasterDataObserver::class])]
class ItemCategory extends Model
{
    use HasFactory, HasUnitScoping;

    protected $fillable = ['unit_id', 'code', 'name', 'description'];

    protected static function newFactory(): \Modules\MasterData\Database\Factories\ItemCategoryFactory
    {
        return \Modules\MasterData\Database\Factories\ItemCategoryFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
