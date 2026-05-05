<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Finance\Observers\AccrueRevenueItemObserver;

#[ObservedBy(AccrueRevenueItemObserver::class)]
class AccrueRevenueItem extends Model
{
    use HasFactory, HasModuleSchema, HasUuids, SoftDeletes;

    protected static function newFactory()
    {
        return \Modules\Finance\Database\Factories\AccrueRevenueItemFactory::new();
    }

    protected $fillable = [
        'accrue_revenue_id',
        'revenue_type_id',
        'amount_estimated',
        'amount_actual',
        'amount_expense_estimated',
        'amount_expense_actual',
        'has_management_fee',
        'invoice_id',
        'work_completion_report_id',
        'description',
        'is_reversed',
    ];

    public function workCompletionReport(): BelongsTo
    {
        return $this->belongsTo(\Modules\Project\Models\WorkCompletionReport::class, 'work_completion_report_id');
    }

    public function revenueType(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\RevenueType::class);
    }

    protected function casts(): array
    {
        return [

            'amount_estimated' => 'decimal:2',
            'amount_actual' => 'decimal:2',
            'amount_expense_estimated' => 'decimal:2',
            'amount_expense_actual' => 'decimal:2',
            'has_management_fee' => 'boolean',
            'is_reversed' => 'boolean',
        ];
    }

    public function accrueRevenue(): BelongsTo
    {
        return $this->belongsTo(AccrueRevenue::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
