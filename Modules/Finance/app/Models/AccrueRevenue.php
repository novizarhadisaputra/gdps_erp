<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Finance\Observers\AccrueRevenueObserver;
use Modules\Project\Models\Project;

#[ObservedBy(AccrueRevenueObserver::class)]
class AccrueRevenue extends Model
{
    use HasFactory, HasModuleSchema, HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'month',
        'year',
        'invoice_id',
        'amount_revenue',
        'amount_cost',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount_revenue' => 'decimal:2',
            'amount_cost' => 'decimal:2',
            'month' => 'integer',
            'year' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
