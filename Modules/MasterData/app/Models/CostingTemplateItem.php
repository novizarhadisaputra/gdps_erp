<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Enums\CostingCategory;

class CostingTemplateItem extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'category' => CostingCategory::class,
    ];

    public function costingTemplate(): BelongsTo
    {
        return $this->belongsTo(CostingTemplate::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
