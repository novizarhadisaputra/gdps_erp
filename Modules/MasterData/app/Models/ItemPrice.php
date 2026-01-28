<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\MasterData\Database\Factories\ItemPriceFactory;

class ItemPrice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'item_id',
        'project_area_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    // protected static function newFactory(): ItemPriceFactory
    // {
    //     // return ItemPriceFactory::new();
    // }
}
