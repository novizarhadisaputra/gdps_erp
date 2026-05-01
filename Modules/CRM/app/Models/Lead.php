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
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Modules\MasterData\Models\BillingOption;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\Tax;
use Modules\Project\Models\Project;
use Modules\Project\Models\ProjectInformation;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(LeadObserver::class)]
class Lead extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes;
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
        'tax_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function minutesOfAgreements(): HasMany
    {
        return $this->hasMany(MinutesOfAgreement::class);
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

    public function profitabilityMonthlies(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysisMonthly::class, 'profitability_analysis_id', 'id');
    }

    public function latestProfitabilityMonthly(): HasOne
    {
        return $this->hasOne(ProfitabilityAnalysisMonthly::class, 'profitability_analysis_id', 'id')->latest('created_at');
    }

    public function latestGeneralInformation(): HasOne
    {
        return $this->hasOne(GeneralInformation::class)->latest('created_at');
    }

    public function projectReviews(): HasMany
    {
        return $this->hasMany(ProjectReview::class);
    }

    public function latestProjectReview(): HasOne
    {
        return $this->hasOne(ProjectReview::class)->latest('created_at');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function cooperationAgreements(): HasMany
    {
        return $this->hasMany(CooperationAgreement::class);
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
                'project_area_id' => $this->salesPlan?->project_area_id ?? $this->project_area_id,
                'product_cluster_id' => $this->salesPlan?->product_cluster_id ?? $this->product_cluster_id,
                'work_scheme_id' => $this->salesPlan?->work_scheme_id ?? $this->work_scheme_id,
                'project_type_id' => $this->salesPlan?->project_type_id ?? $this->project_type_id,
                'payment_term_id' => $this->salesPlan?->payment_term_id ?? $this->payment_term_id,
                'tax_id' => $this->tax_id,
                'start_date' => $this->salesPlan?->start_date ?? $this->start_date,
                'end_date' => $this->salesPlan?->end_date ?? $this->end_date,
                'management_fee_rate' => $this->salesPlan?->management_fee_percentage ?? 0,
                'revenue_segment_id' => $this->revenue_segment_id,
                'status' => ProfitabilityAnalysisStatus::Draft,
            ], array_filter($additionalData, fn ($v) => ! is_null($v))));

            $manualCosts = [];
            $manpowerCategoryId = \Modules\MasterData\Models\DirectCostCategory::where('code', 'manpower')->first()?->id;
            $operationalCategoryId = \Modules\MasterData\Models\DirectCostCategory::where('code', 'tools_equipment')->first()?->id;

            // 1. Manpower Items from Lead's templates
            foreach ($this->manpowerTemplates as $template) {
                $subItems = [];
                foreach ($template->items as $item) {
                    $jp = $item->jobPosition;
                    if (! $jp) {
                        continue;
                    }

                    $breakdown = [];
                    foreach ($jp->remunerationComponents ?? [] as $component) {
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

                    $subItems[] = [
                        'job_position_id' => $jp->id,
                        'name' => $jp->name,
                        'quantity' => $item->quantity ?? 1,
                        'unit_amount' => $item->basic_salary ?? 0,
                        'amount' => ($item->basic_salary ?? 0) * ($item->quantity ?? 1),
                        'cost_breakdown' => $breakdown,
                        'risk_level' => $item->risk_level ?? 'very_low',
                        'employee_type' => $item->employee_type ?? 'ppu',
                        'is_labor_intensive' => (bool) ($item->is_labor_intensive ?? false),
                        'bill_thr_monthly' => (bool) ($item->bill_thr_monthly ?? true),
                        'bill_compensation_monthly' => (bool) ($item->bill_compensation_monthly ?? true),
                        'include_non_fixed_in_accruals' => (bool) ($item->include_non_fixed_in_accruals ?? false),
                        'extra_costs' => $item->extra_costs ?? [],
                    ];
                }

                if (! empty($subItems)) {
                    $manualCosts[] = [
                        'name' => $template->name,
                        'direct_cost_category_id' => $manpowerCategoryId,
                        'costable_type' => ManpowerTemplate::class,
                        'costable_id' => $template->id,
                        'quantity' => 1,
                        'amount' => collect($subItems)->sum('amount'),
                        'sub_items' => $subItems,
                    ];
                }
            }

            // 2. Operational Items from Lead's templates
            foreach ($this->costingTemplates as $template) {
                $subItems = [];
                foreach ($template->costingTemplateItems as $item) {
                    $subItems[] = [
                        'item_id' => $item->item_id,
                        'name' => $item->item?->name ?? 'Unknown',
                        'quantity' => $item->quantity ?? 1,
                        'unit_amount' => $item->unit_price ?? 0,
                        'amount' => ($item->unit_price ?? 0) * ($item->quantity ?? 1),
                        'depreciation_months' => $item->depreciation_months ?? 1,
                    ];
                }

                if (! empty($subItems)) {
                    $manualCosts[] = [
                        'name' => $template->name,
                        'direct_cost_category_id' => $operationalCategoryId,
                        'quantity' => 1,
                        'amount' => collect($subItems)->sum('amount'),
                        'sub_items' => $subItems,
                    ];
                }
            }

            $pa->update([
                'analysis_details' => array_merge($pa->analysis_details ?? [], [
                    'manual_costs' => $manualCosts,
                ]),
            ]);

            return $pa;
        });
    }
}
