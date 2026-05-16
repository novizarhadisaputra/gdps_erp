<?php

namespace Modules\Logistics\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
// use Modules\Logistics\Database\Factories\PurchaseRequestItemFactory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Logistics\Observers\PurchaseRequestItemObserver;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\UnitOfMeasure;

#[ObservedBy(PurchaseRequestItemObserver::class)]
class PurchaseRequestItem extends Model
{
    use HasFactory, HasModuleSchema, HasUuids;

    protected $table = 'purchase_request_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'quantity',
        'unit_of_measure_id',
        'estimated_price',
        'total_estimated_price',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }
}
