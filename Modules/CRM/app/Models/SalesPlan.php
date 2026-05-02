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
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Enums\ProrationMethod;
use Modules\CRM\Observers\SalesPlanObserver;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\SkillCategory;

#[ObservedBy(SalesPlanObserver::class)]
class SalesPlan extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected static function newFactory(): \Modules\CRM\Database\Factories\SalesPlanFactory
    {
        return \Modules\CRM\Database\Factories\SalesPlanFactory::new();
    }

    protected $fillable = [
        'lead_id',
        'project_type_id',
        'revenue_segment_id',
        'industrial_sector_id',
        'skill_category_id',
        'estimated_value',
        'management_fee_percentage',
        'npm_percentage',
        'top_days',
        'start_date',
        'end_date',
        'confidence_level',
        'project_code',
        'proposal_number',
        'po_number',
        'ba_number',
        'so_number',
        'wo_number',
        'contract_number',
        'revenue_distribution_planning',
        'product_cluster_id',
        'project_area_id',
        'ams_id',
        'payment_term_id',
        'job_positions',
        'cutoff_day',
        'proration_method',
    ];

    protected function casts(): array
    {
        return [
            'revenue_distribution_planning' => 'json',
            'start_date' => 'date',
            'end_date' => 'date',
            'confidence_level' => ConfidenceLevel::class,
            'estimated_value' => 'decimal:2',
            'management_fee_percentage' => 'decimal:2',
            'npm_percentage' => 'decimal:2',
            'job_positions' => 'array',
            'cutoff_day' => 'integer',
            'proration_method' => ProrationMethod::class,
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function revenueSegment(): BelongsTo
    {
        return $this->belongsTo(RevenueSegment::class);
    }

    public function industrialSector(): BelongsTo
    {
        return $this->belongsTo(IndustrialSector::class);
    }

    public function skillCategory(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class);
    }

    public function ams(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ams_id');
    }

    public function productCluster(): BelongsTo
    {
        return $this->belongsTo(ProductCluster::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function agreement(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CooperationAgreement::class, 'lead_id', 'lead_id');
    }

    public function workOrder(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WorkOrder::class, 'lead_id', 'lead_id');
    }

    public function monthlyBreakdowns(): HasMany
    {
        return $this->hasMany(SalesPlanMonthly::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\PaymentTerm::class);
    }

    public function toGeneralInformation(): GeneralInformation
    {
        $this->loadMissing(['lead.customer', 'lead.user', 'ams']);
        $lead = $this->lead;

        $gi = $lead->generalInformations()->create([
            'customer_id' => $lead->customer_id,
            'project_area_id' => $this->project_area_id,
            'estimated_start_date' => $this->start_date,
            'estimated_end_date' => $this->end_date,
            'scope_of_work' => $lead->title,
            'description' => $lead->description,
            'sales_plan_id' => $this->id,
            'status' => GeneralInformationStatus::Draft,
        ]);

        // Sync Customer Contacts from Master Data
        foreach (($lead->customer?->contacts ?? []) as $contact) {
            $gi->pics()->create([
                'contact_role_id' => $contact['type'] ?? null,
                'name' => $contact['name'] ?? null,
                'phone' => $contact['phone'] ?? null,
                'email' => $contact['email'] ?? null,
            ]);
        }

        return $gi;
    }
}
