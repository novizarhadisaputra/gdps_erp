<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\SalesOrder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\CRM\Models\CommunicationLog;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Observers\InvoiceObserver;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Modules\Project\Models\WorkCompletionReport;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(InvoiceObserver::class)]
class Invoice extends Model implements HasMedia
{
    use HasDigitalSignatures;
    use HasFactory;
    use HasModuleSchema;
    use HasUuids;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'sales_order_id',
        'work_completion_report_id',
        'customer_id',
        'invoice_number',
        'sequence_number',
        'year',
        'invoice_date',
        'due_date',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'payment_info',
        'items',
        'content_config',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'payment_info' => 'array',
            'items' => 'array',
            'content_config' => 'array',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function workCompletionReport(): BelongsTo
    {
        return $this->belongsTo(WorkCompletionReport::class, 'work_completion_report_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function communicationLogs(): MorphMany
    {
        return $this->morphMany(CommunicationLog::class, 'emailable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proof')
            ->useDisk('s3')
            ->singleFile();
            
        $this->addMediaCollection('draft_invoice')
            ->useDisk('s3')
            ->singleFile();
    }
}
