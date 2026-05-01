<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\Customer;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Observers\AccrueRevenueObserver;
use Modules\MasterData\Models\ProjectArea;
use Modules\Project\Models\Project;

#[ObservedBy(AccrueRevenueObserver::class)]
class AccrueRevenue extends Model
{
    use HasFactory, HasModuleSchema, HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'customer_id',
        'project_area_id',
        'number',
        'sequence_number',
        'company_code',
        'month',
        'year',
        'total_amount_estimated',
        'total_amount_actual',
        'total_amount_expense_estimated',
        'total_amount_expense_actual',
        'description',
        'status',
        'work_period',
        'accrual_period',
        'sap_reference',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'work_period' => 'date',
            'accrual_period' => 'date',
            'status' => AccrueRevenueStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AccrueRevenueItem::class);
    }
}
