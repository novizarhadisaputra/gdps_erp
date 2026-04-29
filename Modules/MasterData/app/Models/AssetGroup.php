<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Database\Factories\AssetGroupFactory;
use Modules\MasterData\Enums\AssetGroupType;
use Modules\MasterData\Traits\HasDefaultRecord;

class AssetGroup extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected static function newFactory(): AssetGroupFactory
    {
        return AssetGroupFactory::new();
    }

    protected $fillable = [
        'name',
        'type',
        'useful_life_years',
        'rate_straight_line',
        'rate_declining_balance',
        'description',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssetGroupType::class,
            'useful_life_years' => 'integer',
            'rate_straight_line' => 'decimal:2',
            'rate_declining_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function itemCategories(): HasMany
    {
        return $this->hasMany(ItemCategory::class);
    }
}
