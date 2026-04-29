<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Database\Factories\ItemCategoryFactory;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;
use Modules\MasterData\Traits\HasDefaultRecord;

class ItemCategory extends Model
{
    use HasAutoCodeAndSlug, HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'asset_group_id',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): ItemCategoryFactory
    {
        return ItemCategoryFactory::new();
    }

    public function assetGroup(): BelongsTo
    {
        return $this->belongsTo(AssetGroup::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
