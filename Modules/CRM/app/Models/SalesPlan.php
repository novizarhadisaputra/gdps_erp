<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CRM\Observers\SalesPlanObserver;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\ServiceLine;
use Modules\MasterData\Models\SkillCategory;

#[ObservedBy(SalesPlanObserver::class)]
class SalesPlan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'lead_id',
        'project_type_id',
        'revenue_segment_id',
        'service_line_id',
        'industrial_sector_id',
        'skill_category_id',
        'industry',
        'estimated_value',
        'management_fee_percentage',
        'margin_percentage',
        'top_days',
        'start_date',
        'end_date',
        'priority_level',
        'confidence_level',
        'project_code',
        'proposal_number',
        'document_reference',
        'revenue_distribution_planning',
    ];

    protected function casts(): array
    {
        return [
            'revenue_distribution_planning' => 'json',
            'start_date' => 'date',
            'end_date' => 'date',
            'estimated_value' => 'decimal:2',
            'management_fee_percentage' => 'decimal:2',
            'margin_percentage' => 'decimal:2',
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

    public function serviceLine(): BelongsTo
    {
        return $this->belongsTo(ServiceLine::class);
    }

    public function industrialSector(): BelongsTo
    {
        return $this->belongsTo(IndustrialSector::class);
    }

    public function skillCategory(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class);
    }

    public function monthlyBreakdowns(): HasMany
    {
        return $this->hasMany(SalesPlanMonthly::class);
    }
}
