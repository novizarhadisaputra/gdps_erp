<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Traits\HasUnitScoping;

#[ObservedBy([\Modules\MasterData\Observers\MasterDataObserver::class])]
class Item extends Model
{
    use HasFactory, HasUnitScoping;

    protected $fillable = [
        'unit_id',
        'item_category_id',
        'unit_of_measure_id',
        'code',
        'name',
        'description',
        'price',
        'depreciation_rate',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): \Modules\MasterData\Database\Factories\ItemFactory
    {
        return \Modules\MasterData\Database\Factories\ItemFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_of_measure_id');
    }

    public function itemPrices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }

    public function getPriceForArea(?int $areaId = null): float
    {
        if ($areaId) {
            $areaPrice = $this->itemPrices->where('project_area_id', $areaId)->first();
            if ($areaPrice) {
                return (float) $areaPrice->price;
            }
        }

        return (float) $this->price;
    }
}
