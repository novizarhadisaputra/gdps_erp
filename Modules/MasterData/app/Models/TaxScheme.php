<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;
use Modules\MasterData\Traits\HasDefaultRecord;

class TaxScheme extends Model
{
    use HasAutoCodeAndSlug, HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'scheme_code', // skema_1, skema_2a, skema_2b, skema_2c, skema_2d, skema_3, skema_4, skema_5
        'rate_percentage',
        'notes',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'rate_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
