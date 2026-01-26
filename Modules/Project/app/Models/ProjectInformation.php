<?php

namespace Modules\Project\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectInformation extends Model
{
    use HasFactory;

    protected $table = 'project_informations';

    protected $fillable = [
        'project_id',
        'pic_client_name',
        'pic_client_phone',
        'risk_management',
        'direct_cost',
        'process_date',
        'status',
        'start_date',
        'end_date',
        'description',
        'feasibility_study',
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
        'pic_finance_name',
        'pic_finance_phone',
        'pic_finance_email',
        'max_invoice_send_date',
        'material_equipment_details',
        'management_fee_per_month',
        'ppn_percentage',
        'manpower_cleaner',
        'manpower_leader_cleaner',
        'manpower_engineer',
        'manpower_security',
        'remuneration_details',
        'payroll_date',
        'overtime_cut_off_date',
    ];

    protected function casts(): array
    {
        return [
            'risk_management' => 'array',
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
            'feasibility_study' => 'array',
            'profitability_analysis' => 'array',
            'material_equipment_details' => 'array',
            'remuneration_details' => 'array',
            'manpower_cleaner' => 'integer',
            'manpower_leader_cleaner' => 'integer',
            'manpower_engineer' => 'integer',
            'manpower_security' => 'integer',
        ];
    }

    protected static function newFactory(): \Modules\Project\Database\Factories\ProjectInformationFactory
    {
        return \Modules\Project\Database\Factories\ProjectInformationFactory::new();
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function paymentTerm(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\PaymentTerm::class);
    }

    public function projectType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\ProjectType::class);
    }

    public function billingOption(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\BillingOption::class);
    }

    public function oprep(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\Employee::class, 'oprep_id');
    }

    public function ams(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\Employee::class, 'ams_id');
    }
}
