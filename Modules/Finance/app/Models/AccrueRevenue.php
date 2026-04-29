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
        'total_amount_estimated',
        'total_amount_actual',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccrueRevenueItem::class);
    }
}
