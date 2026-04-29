<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;
use Modules\MasterData\Traits\HasDefaultRecord;

class PartnerFeeType extends Model
{
    use HasAutoCodeAndSlug, HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'calculation_basis', // per_output, per_hour, per_day, flat, percentage
        'is_taxable',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
