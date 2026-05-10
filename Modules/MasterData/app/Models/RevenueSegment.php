<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\RevenueSegmentFactory;
use Modules\MasterData\Observers\RevenueSegmentObserver;
use Modules\MasterData\Traits\HasDefaultRecord;

#[ObservedBy(RevenueSegmentObserver::class)]
class RevenueSegment extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected static function newFactory(): RevenueSegmentFactory
    {
        return RevenueSegmentFactory::new();
    }

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
}
