<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\TaxFactory;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;
use Modules\MasterData\Traits\HasDefaultRecord;

// use Modules\MasterData\Database\Factories\TaxFactory;

class Tax extends Model
{
    use HasAutoCodeAndSlug, HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'calculation_type',
        'rate',
        'base_rate_numerator',
        'base_rate_denominator',
        'is_active',
        'is_default',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'base_rate_numerator' => 'integer',
            'base_rate_denominator' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): TaxFactory
    {
        return TaxFactory::new();
    }
}
