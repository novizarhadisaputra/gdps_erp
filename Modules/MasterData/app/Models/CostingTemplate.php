<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostingTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function costingTemplateItems(): HasMany
    {
        return $this->hasMany(CostingTemplateItem::class);
    }
}
