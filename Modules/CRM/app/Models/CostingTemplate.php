<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostingTemplate extends Model
{
    use HasFactory, HasUuids;

    protected static function newFactory()
    {
        return \Modules\CRM\Database\Factories\CostingTemplateFactory::new();
    }

    protected $guarded = [];

    public function lead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function costingTemplateItems(): HasMany
    {
        return $this->hasMany(CostingTemplateItem::class);
    }

    public function pic(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_id');
    }

    public function getTotalMonthlyCost(): float
    {
        return (float) $this->costingTemplateItems()->sum('monthly_cost');
    }
}
