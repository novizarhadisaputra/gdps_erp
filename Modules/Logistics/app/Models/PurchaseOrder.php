<?php

namespace Modules\Logistics\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
// use Modules\Logistics\Database\Factories\PurchaseOrderFactory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\CommunicationLog;
use Modules\Logistics\Enums\PurchaseOrderStatus;
use Modules\Logistics\Observers\PurchaseOrderObserver;
use Modules\MasterData\Models\Vendor;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Modules\Project\Models\Project;

#[ObservedBy(PurchaseOrderObserver::class)]
class PurchaseOrder extends Model
{
    use HasDigitalSignatures, HasFactory, HasModuleSchema, HasUuids, SoftDeletes;

    protected $table = 'purchase_orders';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'po_number',
        'purchase_request_id',
        'vendor_id',
        'project_id',
        'total_amount',
        'tax_amount',
        'grand_total',
        'warehouse_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseOrderStatus::class,
        ];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function communicationLogs(): MorphMany
    {
        return $this->morphMany(CommunicationLog::class, 'emailable');
    }
}
