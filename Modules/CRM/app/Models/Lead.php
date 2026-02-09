<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\LeadFactory;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Observers\LeadObserver;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\BillingOption;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Models\ProjectInformation;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(LeadObserver::class)]
class Lead extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

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
        'service_line_id',
        'industrial_sector_id',
        'project_area_id',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
        'confidence_level' => 'string',
        'estimated_amount' => 'decimal:2',
        'probability' => 'integer',
        'expected_closing_date' => 'date',
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

    public function salesPlan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SalesPlan::class);
    }
}
