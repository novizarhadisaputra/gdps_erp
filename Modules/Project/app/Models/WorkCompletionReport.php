<?php

namespace Modules\Project\Models;

use App\Models\Comment;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\SalesOrder;
use Modules\Finance\Models\Invoice;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Modules\CRM\Models\CommunicationLog;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Observers\WorkCompletionReportObserver;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(WorkCompletionReportObserver::class)]
class WorkCompletionReport extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasModuleSchema, HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'project_id',
        'sales_order_id',
        'customer_id',
        'report_number',
        'sequence_number',
        'year',
        'document_date',
        'service_period_start',
        'service_period_end',
        'work_progress_percentage',
        'description',
        'items',
        'total_amount',
        'status',
        'content_config',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'service_period_start' => 'date',
            'service_period_end' => 'date',
            'work_progress_percentage' => 'decimal:2',
            'items' => 'array',
            'status' => WorkCompletionStatus::class,
            'content_config' => 'array',
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

    public function communicationLogs(): MorphMany
    {
        return $this->morphMany(CommunicationLog::class, 'emailable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('draft_report')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('signed_report')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('completion_documents')
            ->useDisk('s3')
            ->singleFile();
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }
}
