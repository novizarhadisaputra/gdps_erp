<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CRM\Enums\CostingCategory;
use Modules\CRM\Enums\DepreciationMethod;
use Modules\MasterData\Models\Item;

class CostingTemplateItem extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'category' => CostingCategory::class,
            'depreciation_method' => DepreciationMethod::class,
        ];
    }

    public function costingTemplate(): BelongsTo
    {
        return $this->belongsTo(CostingTemplate::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
