<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Database\Factories\ItemFactory;
use Modules\MasterData\Observers\MasterDataObserver;
use Modules\MasterData\Traits\HasUnitScoping;

#[ObservedBy([MasterDataObserver::class])]
class Item extends Model
{
    use HasFactory, HasUnitScoping, HasUuids;

    protected $fillable = [
        'unit_id',
        'item_category_id',
        'unit_of_measure_id',
        'code',
        'name',
        'description',
        'price',
        'depreciation_months',
        'price_period_start',
        'price_period_end',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'depreciation_months' => 'integer',
        'price_period_start' => 'date',
        'price_period_end' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): ItemFactory
    {
        return ItemFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_of_measure_id');
    }

    public function itemPrices(): HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }

    public function getPriceForArea(?string $areaId = null): float
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
