<?php

namespace Modules\Project\Models;

use App\Models\Comment;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\BillingOption;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Enums\ProjectStatus;
use Modules\Project\Observers\ProjectObserver;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// use Modules\Project\Database\Factories\ProjectFactory;

#[ObservedBy(ProjectObserver::class)]
class Project extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'number',
        'name',
        'status',
        'customer_id',
        'sourceable_id',
        'sourceable_type',
        'project_number',
        'work_scheme_id',
        'product_cluster_id',
        'tax_id',
        'payment_term_id',
        'project_type_id',
        'billing_option_id',
        'oprep_id',
        'ams_id',
        'project_area_id',
        'start_date',
        'end_date',
        'proposal_id',
        'profitability_analysis_id',
        'lead_id',
        'revenue_segment_id',
        'progress_percentage',
    ];

    public static function generateProjectCode(self $project): string
    {
        // Formula: [Customer(3)][Sequence(2)][Area(3)][WorkScheme(2)][ProductCluster(4)][Tax(2)]
        $project->loadMissing(['customer', 'projectArea', 'productCluster', 'tax', 'workScheme', 'projectType']);

        $getCode = function ($relation, $default, $label) use ($project) {
            $code = $project->{$relation}?->code;

            if (! $code) {
                return $default;
            }

            return strtoupper($code);
        };

        $customerCode = $getCode('customer', 'UNK', 'Customer');
        $projectNum = str_pad((string) ($project->project_number ?? '1'), 2, '0', STR_PAD_LEFT);
        $areaCode = $getCode('projectArea', 'UNK', 'Area');
        $schemeCode = $getCode('workScheme', '00', 'WorkScheme');
        $clusterCode = $getCode('productCluster', 'UNK', 'Cluster');
        $taxCode = $getCode('tax', 'P0', 'Tax');

        return "{$customerCode}{$projectNum}{$areaCode}{$schemeCode}{$clusterCode}{$taxCode}";
    }

    public static function generateProjectNumber(self $project): string
    {
        return self::generateProjectCode($project);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'progress_percentage' => 'decimal:2',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('project_documents')
            ->useDisk('s3');

        $this->addMediaCollection('deliverables')
            ->useDisk('s3');
    }

    public function information(): HasOne
    {
        return $this->hasOne(ProjectInformation::class);
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function workCompletionReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkCompletionReport::class);
    }

    public function salesOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\CRM\Models\SalesOrder::class);
    }

    public function sourceable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function dailyReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function changeRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectChangeRequest::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }

    protected static function newFactory()
    {
        return \Modules\Project\Database\Factories\ProjectFactory::new();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workScheme(): BelongsTo
    {
        return $this->belongsTo(WorkScheme::class);
    }

    public function productCluster(): BelongsTo
    {
        return $this->belongsTo(ProductCluster::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function billingOption(): BelongsTo
    {
        return $this->belongsTo(BillingOption::class);
    }

    public function oprep(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'oprep_id');
    }

    public function ams(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'ams_id');
    }

    public function revenueSegment(): BelongsTo
    {
        return $this->belongsTo(RevenueSegment::class);
    }

    public function getAmountAttribute(): float
    {
        return $this->proposal?->amount
            ?? $this->profitabilityAnalysis?->revenue_per_month
            ?? 0.0;
    }

    public function getCodeAttribute(): string
    {
        return $this->number ?? '';
    }
}
