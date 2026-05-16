<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Observers\RevenueTypeObserver;
use Modules\MasterData\Traits\HasDefaultRecord;

#[ObservedBy(RevenueTypeObserver::class)]
class RevenueType extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected static function newFactory()
    {
        return \Modules\MasterData\Database\Factories\RevenueTypeFactory::new();
    }

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'is_default',
        'applicable_to',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'applicable_to' => 'array',
        ];
    }
}
