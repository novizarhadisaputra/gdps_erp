<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([\Modules\MasterData\Observers\MasterDataObserver::class])]
class ItemCategory extends Model
{
    protected $fillable = ['code', 'name', 'description'];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
