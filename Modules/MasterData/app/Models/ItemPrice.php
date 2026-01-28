<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\MasterData\Database\Factories\ItemPriceFactory;

class ItemPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'project_area_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function projectArea(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\ProjectArea::class);
    }

    // protected static function newFactory(): ItemPriceFactory
    // {
    //     // return ItemPriceFactory::new();
    // }
}
