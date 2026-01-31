<?php

namespace Modules\Project\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Models\BillingOption;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Traits\HasDigitalSignatures;

use Modules\Project\Database\Factories\ProjectInformationFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Modules\Project\Observers\ProjectInformationObserver;

#[ObservedBy(ProjectInformationObserver::class)]
class ProjectInformation extends Model
{
    use HasDigitalSignatures, HasFactory, HasUuids;

    protected $table = 'project_informations';

    protected $fillable = [
        'project_id',
        'lead_id',
        'direct_cost',
        'process_date',
        'status',
        'start_date',
        'end_date',
        'description',
        'profitability_analysis',
        'payment_term_id',
        'project_type_id',
        'billing_option_id',
        'oprep_id',
        'ams_id',
        'remarks',
        'ipk_status',
        'thr_status',
        'previous_code',
        'operational_visit_schedule',
        'bapp_cut_off_date',
        'revenue_per_month',
        'max_invoice_send_date',
        'management_fee_per_month',
        'ppn_percentage',
        'analysis_details',
        'remuneration_details',
        'payroll_date',
        'overtime_cut_off_date',
        'signatures',
    ];

    protected function casts(): array
    {
        return [
            'process_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'bapp_cut_off_date' => 'date',
            'max_invoice_send_date' => 'date',
            'payroll_date' => 'date',
            'overtime_cut_off_date' => 'date',
            'direct_cost' => 'decimal:2',
            'revenue_per_month' => 'decimal:2',
            'management_fee_per_month' => 'decimal:2',
            'ppn_percentage' => 'decimal:2',
            'profitability_analysis' => 'array',
            'analysis_details' => 'array',
            'remuneration_details' => 'array',
            'signatures' => 'array',
        ];
    }

    protected static function newFactory(): ProjectInformationFactory
    {
        return ProjectInformationFactory::new();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
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
}
