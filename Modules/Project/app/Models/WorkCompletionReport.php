<?php

namespace Modules\Project\Models;

use Modules\MasterData\Traits\HasDigitalSignatures;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\SalesOrder;
use Modules\Project\Enums\WorkCompletionStatus;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class WorkCompletionReport extends Model implements HasMedia
{
    use HasDigitalSignatures;
    use HasFactory;
    use HasUuids;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'sales_order_id',
        'customer_id',
        'report_number',
        'document_date',
        'service_period_start',
        'service_period_end',
        'work_progress_percentage',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'service_period_start' => 'date',
            'service_period_end' => 'date',
            'work_progress_percentage' => 'decimal:2',
            'status' => WorkCompletionStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('completion_documents')
            ->useDisk('s3')
            ->singleFile();
    }
}
