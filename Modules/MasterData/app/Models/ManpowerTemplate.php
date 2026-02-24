<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManpowerTemplate extends Model
{
    use HasFactory, HasUuids;

    protected static function newFactory()
    {
        return \Modules\MasterData\Database\Factories\ManpowerTemplateFactory::new();
    }

    protected $fillable = [
        'project_area_id',
        'name',
        'description',
        'risk_level',
        'is_labor_intensive',
        'employee_type',
        'bill_thr_monthly',
        'bill_compensation_monthly',
        'is_active',
    ];

    protected $casts = [
        'is_labor_intensive' => 'boolean',
        'bill_thr_monthly' => 'boolean',
        'bill_compensation_monthly' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManpowerTemplateItem::class);
    }
}
