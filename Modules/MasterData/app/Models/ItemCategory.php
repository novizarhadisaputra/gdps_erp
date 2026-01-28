<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Database\Factories\ItemCategoryFactory;
use Modules\MasterData\Observers\MasterDataObserver;
use Modules\MasterData\Traits\HasUnitScoping;

#[ObservedBy(MasterDataObserver::class)]
class ItemCategory extends Model
{
    use HasFactory, HasUnitScoping, HasUuids;

    protected $fillable = ['unit_id', 'code', 'name', 'description'];

    protected static function newFactory(): ItemCategoryFactory
    {
        return ItemCategoryFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
