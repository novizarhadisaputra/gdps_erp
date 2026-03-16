<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\RevenueSegmentFactory;

class RevenueSegment extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected static function newFactory(): RevenueSegmentFactory
    {
        return RevenueSegmentFactory::new();
    }

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
