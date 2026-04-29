<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Database\Factories\UnitOfMeasureFactory;
use Modules\MasterData\Traits\HasDefaultRecord;

class UnitOfMeasure extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected $table = 'units_of_measure';

    protected $fillable = [
        'name',
        'code',
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

    protected static function newFactory(): UnitOfMeasureFactory
    {
        return UnitOfMeasureFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
