<?php

namespace Modules\Project\Models;

use App\Models\Comment;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\CommunicationLog;
use Modules\CRM\Models\Customer;
use Modules\Finance\Models\Invoice;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Observers\WorkCompletionReportObserver;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(WorkCompletionReportObserver::class)]
class WorkCompletionReport extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasModuleSchema, HasTranslations, HasUuids, InteractsWithMedia, SoftDeletes;

    public array $translatable = [
        'description',
        'tax_wording',
        'items',
    ];

    protected $fillable = [
        'project_id',
        'customer_id',
        'project_area_id',
        'number',
        'sequence_number',
        'revision_number',
        'previous_code',
        'year',
        'document_date',
        'service_period_start',
        'service_period_end',
        'work_progress_percentage',
        'description',
        'items',
        'total_amount',
        'status',
        'sourceable_id',
        'sourceable_type',
        'tax_id',
        'tax_percentage',
        'tax_basis',
        'tax_base_amount',
        'tax_amount',
        'tax_wording',
        'content_config',
        'snapshot',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'service_period_start' => 'date',
            'service_period_end' => 'date',
            'work_progress_percentage' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_base_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'items' => 'array',
            'status' => WorkCompletionStatus::class,
            'content_config' => 'array',
            'snapshot' => 'array',
        ];
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(WorkCompletionReportRevision::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function accrueRevenueItems(): HasMany
    {
        return $this->hasMany(\Modules\Finance\Models\AccrueRevenueItem::class);
    }
}
