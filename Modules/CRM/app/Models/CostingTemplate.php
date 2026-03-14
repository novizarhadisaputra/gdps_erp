<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CRM\Observers\CostingTemplateObserver;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(CostingTemplateObserver::class)]
class CostingTemplate extends Model implements HasMedia
{
    use HasFactory, HasUuids;
    use HasModuleSchema;
    use InteractsWithMedia;

    protected static function newFactory(): \Modules\CRM\Database\Factories\CostingTemplateFactory
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

    public function refreshTotals(): void
    {
        $this->update([
            'total_amount' => (float) $this->costingTemplateItems()->sum('total_price'),
            'total_monthly_cost' => (float) $this->costingTemplateItems()->sum('monthly_cost'),
        ]);
    }
}
