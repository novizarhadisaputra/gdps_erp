<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Models\ProjectArea;

class ManpowerTemplate extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected static function newFactory(): \Modules\CRM\Database\Factories\ManpowerTemplateFactory
    {
        return \Modules\CRM\Database\Factories\ManpowerTemplateFactory::new();
    }

    protected $fillable = [
        'lead_id',
        'project_area_id',
        'name',
        'description',
        'risk_level',
        'is_labor_intensive',
        'employee_type',
        'bill_thr_monthly',
        'bill_compensation_monthly',
        'is_active',
        'is_imported',
        'import_source_id',
    ];

    protected $casts = [
        'is_labor_intensive' => 'boolean',
        'bill_thr_monthly' => 'boolean',
        'bill_compensation_monthly' => 'boolean',
        'is_active' => 'boolean',
        'is_imported' => 'boolean',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManpowerTemplateItem::class);
    }
}
