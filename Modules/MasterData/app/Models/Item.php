<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Database\Factories\ItemFactory;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;
use Modules\MasterData\Traits\HasUnitScoping;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Item extends Model implements HasMedia
{
    use HasAutoCodeAndSlug, HasFactory, HasUnitScoping, HasUuids, InteractsWithMedia;
    use HasModuleSchema;

    protected $fillable = [
        'unit_id',
        'item_category_id',
        'asset_group_id',
        'unit_of_measure_id',
        'code',
        'name',
        'description',
        'price',
        'depreciation_months',
        'price_valid_at',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'depreciation_months' => 'integer',
        'price_valid_at' => 'date',
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

    public function assetGroup(): BelongsTo
    {
        return $this->belongsTo(AssetGroup::class);
    }

    public function itemPrices(): HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
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
