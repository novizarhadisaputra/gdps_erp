<?php

namespace Modules\CRM\Models;

use App\Models\User;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Database\Factories\LeadFactory;
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Observers\LeadObserver;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\BillingOption;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Models\Project;
use Modules\Project\Models\ProjectInformation;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(LeadObserver::class)]
class Lead extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;
    use HasModuleSchema;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'title',
        'customer_id',
        'work_scheme_id',
        'status',
        'confidence_level',
        'estimated_amount',
        'probability',
        'expected_closing_date',
        'position',
        'description',
        'user_id',
        'revenue_segment_id',
        'product_cluster_id',
        'project_type_id',
        'industrial_sector_id',
        'project_area_id',
        'start_date',
        'end_date',
        'job_positions',
        'pic_costing_id',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
        'confidence_level' => ConfidenceLevel::class,
        'estimated_amount' => 'decimal:2',
        'job_positions' => 'array',
        'probability' => 'integer',
        'expected_closing_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workScheme(): BelongsTo
    {
        return $this->belongsTo(WorkScheme::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function generalInformations(): HasMany
    {
        return $this->hasMany(GeneralInformation::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function projectInformations(): HasMany
    {
        return $this->hasMany(ProjectInformation::class);
    }

    public function profitabilityAnalyses(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysis::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function productCluster(): BelongsTo
    {
        return $this->belongsTo(ProductCluster::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
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

    public function picCosting(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_costing_id');
    }

    public function salesPlan(): HasOne
    {
        return $this->hasOne(SalesPlan::class);
    }

    public function costingTemplates(): HasMany
    {
        return $this->hasMany(CostingTemplate::class);
    }

    public function manpowerTemplates(): HasMany
    {
        return $this->hasMany(ManpowerTemplate::class);
    }

    public function revenueSegment(): BelongsTo
    {
        return $this->belongsTo(RevenueSegment::class);
    }

    public function industrialSector(): BelongsTo
    {
        return $this->belongsTo(IndustrialSector::class);
    }

    public function createProfitabilityAnalysis(array $additionalData = []): ProfitabilityAnalysis
    {
        return DB::transaction(function () use ($additionalData) {
            // Ensure necessary relations are loaded to avoid N+1 issues
            $this->loadMissing([
                'salesPlan',
                'manpowerTemplates.items.jobPosition.remunerationComponents',
                'costingTemplates.costingTemplateItems.item',
            ]);

            $pa = ProfitabilityAnalysis::create(array_merge([
                'lead_id' => $this->id,
                'customer_id' => $this->customer_id,
                'work_scheme_id' => $this->work_scheme_id,
                'project_area_id' => $this->project_area_id,
                'product_cluster_id' => $this->product_cluster_id,
                'payment_term_id' => $this->payment_term_id ?? $this->salesPlan?->payment_term_id,
                'management_fee_rate' => $this->salesPlan?->management_fee_percentage ?? 0,
                'status' => ProfitabilityAnalysisStatus::Draft,
            ], $additionalData));

            // 1. Manpower Items from Lead's templates
            foreach ($this->manpowerTemplates as $template) {
                foreach ($template->items as $item) {
                    $jp = $item->jobPosition;
                    if (! $jp) {
                        continue;
                    }

                    $breakdown = [];
                    foreach ($jp->remunerationComponents ?? [] as $component) {
                        // Skip 'Gaji Pokok' because it is already handled by unit_cost_price
                        if (str_contains(strtolower($component->name), 'gaji pokok')) {
                            continue;
                        }

                        $breakdown[] = [
                            'name' => $component->name,
                            'type' => 'nominal',
                            'value' => $component->pivot->amount,
                            'is_fixed' => (bool) $component->is_fixed,
                        ];
                    }

                    $pa->items()->create([
                        'costable_type' => JobPosition::class,
                        'costable_id' => $jp->id,
                        'quantity' => $item->quantity ?? 1,
                        'unit_cost_price' => $item->basic_salary ?? 0,
                        'duration_months' => 1,
                        'depreciation_months' => 1,
                        'cost_breakdown' => $breakdown,
                    ]);
                }
            }

            // 2. Operational Items from Lead's templates
            foreach ($this->costingTemplates as $template) {
                foreach ($template->costingTemplateItems as $item) {
                    $pa->items()->create([
                        'costable_type' => Item::class,
                        'costable_id' => $item->item_id,
                        'quantity' => $item->quantity ?? 1,
                        'unit_cost_price' => $item->unit_price ?? 0,
                        'duration_months' => 1,
                        'depreciation_months' => $item->depreciation_months ?? 1,
                    ]);
                }
            }

            return $pa;
        });
    }
}
