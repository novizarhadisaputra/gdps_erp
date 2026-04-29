<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;
use Modules\MasterData\Traits\HasDefaultRecord;

class FixedAllowance extends Model
{
    use HasAutoCodeAndSlug, HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'is_bpjs_base',
        'is_taxable',
        'default_amount',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_bpjs_base' => 'boolean',
            'is_taxable' => 'boolean',
            'default_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
