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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('source_file')
            ->useDisk('s3')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
            ]);
    }

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
        $totalAmount = (float) $this->costingTemplateItems()->sum('total_price');
        $totalCost = (float) $this->costingTemplateItems()->selectRaw('SUM(quantity * unit_price) as total_cost')->value('total_cost');

        $margin = 0;
        if ($totalAmount > 0) {
            $margin = (($totalAmount - $totalCost) / $totalAmount) * 100;
        }

        $this->update([
            'total_cost_amount' => $totalCost,
            'total_amount' => $totalAmount,
            'total_monthly_cost' => (float) $this->costingTemplateItems()->sum('monthly_cost'),
            'margin_percentage' => round($margin, 2),
        ]);
    }
}
