<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Models\JobPosition;

class ManpowerTemplateItem extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected static function newFactory(): \Modules\CRM\Database\Factories\ManpowerTemplateItemFactory
    {
        return \Modules\CRM\Database\Factories\ManpowerTemplateItemFactory::new();
    }

    protected $fillable = [
        'manpower_template_id',
        'job_position_id',
        'quantity',
        'basic_salary',
        'notes',
        'risk_level',
        'employee_type',
        'is_labor_intensive',
        'bill_thr_monthly',
        'bill_compensation_monthly',
        'include_non_fixed_in_accruals',
        'extra_costs',
        'allowances',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'basic_salary' => 'decimal:2',
            'is_labor_intensive' => 'boolean',
            'bill_thr_monthly' => 'boolean',
            'bill_compensation_monthly' => 'boolean',
            'include_non_fixed_in_accruals' => 'boolean',
            'extra_costs' => 'array',
            'allowances' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ManpowerTemplate::class, 'manpower_template_id');
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }
}
