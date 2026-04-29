<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxPasal17Rate extends Model
{
    use HasFactory, HasModuleSchema, HasUuids, SoftDeletes;

    protected $fillable = [
        'min_amount',
        'max_amount',
        'rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
