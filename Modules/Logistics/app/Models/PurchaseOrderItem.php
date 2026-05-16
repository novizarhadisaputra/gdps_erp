<?php

namespace Modules\Logistics\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
// use Modules\Logistics\Database\Factories\PurchaseOrderItemFactory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Logistics\Observers\PurchaseOrderItemObserver;
use Modules\MasterData\Models\Item;

#[ObservedBy(PurchaseOrderItemObserver::class)]
class PurchaseOrderItem extends Model
{
    use HasFactory, HasModuleSchema, HasUuids;

    protected $table = 'purchase_order_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'quantity',
        'received_quantity',
        'unit_price',
        'tax_rate',
        'total_price',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
