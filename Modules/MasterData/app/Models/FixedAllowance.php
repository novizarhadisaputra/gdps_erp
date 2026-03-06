<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAllowance extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected $fillable = [
        'name',
        'is_bpjs_base',
        'is_taxable',
        'default_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_bpjs_base' => 'boolean',
            'is_taxable' => 'boolean',
            'default_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
