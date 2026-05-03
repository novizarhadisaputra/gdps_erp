<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\SalesOrderFactory;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Enums\SalesOrderType;
use Modules\CRM\Observers\SalesOrderObserver;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Modules\Project\Models\Project;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(SalesOrderObserver::class)]
class SalesOrder extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasModuleSchema, HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'number',
        'order_date',
        'project_id',
        'sourceable_id',
        'sourceable_type',
        'proposal_id',
        'customer_id',
        'type',
        'status',
        'amount',
        'management_fee_percentage',
        'tax_percentage',
        'tax_id',
        'sales_pic_id',
        'project_manager_id',
        'service_type',
        'job_location',
        'manpower_initial_qty',
        'manpower_composition',
        'payment_terms',
        'probation_period',
        'replacement_sla',
        'reporting_schedule',
        'sequence_number',
        'year',
        'content_config',
        'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'type' => SalesOrderType::class,
            'status' => SalesOrderStatus::class,
            'amount' => 'decimal:2',
            'management_fee_percentage' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'manpower_initial_qty' => 'integer',
            'manpower_composition' => 'array',
            'content_config' => 'array',
            'snapshot' => 'array',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('draft_so')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('signed_so')
            ->useDisk('s3')
            ->singleFile();
    }

    protected static function newFactory(): SalesOrderFactory
    {
        return SalesOrderFactory::new();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesPic(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'sales_pic_id');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'project_manager_id');
    }

    public function amendments(): HasMany
    {
        return $this->hasMany(SalesOrderAmendment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function communicationLogs(): MorphMany
    {
        return $this->morphMany(CommunicationLog::class, 'emailable');
    }

    public function sourceable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function getProfitabilityAnalysisAttribute(): ?ProfitabilityAnalysis
    {
        return $this->project?->profitabilityAnalysis ?? $this->proposal?->profitabilityAnalysis;
    }
}
