<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Enums\AssetGroupType;

class AssetGroup extends Model
{
    use HasFactory, HasUuids;

    protected static function newFactory()
    {
        return \Modules\MasterData\Database\Factories\AssetGroupFactory::new();
    }

    protected $fillable = [
        'name',
        'type',
        'useful_life_years',
        'rate_straight_line',
        'rate_declining_balance',
        'description',
    ];

    protected $casts = [
        'type' => AssetGroupType::class,
        'useful_life_years' => 'integer',
        'rate_straight_line' => 'decimal:2',
        'rate_declining_balance' => 'decimal:2',
    ];

    public function itemCategories(): HasMany
    {
        return $this->hasMany(ItemCategory::class);
    }
}
