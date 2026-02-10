<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

// use Modules\MasterData\Database\Factories\RemunerationComponentFactory;

class RemunerationComponent extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type',
        'default_amount',
        'is_bpjs_base',
        'is_taxable',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'is_bpjs_base' => 'boolean',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
