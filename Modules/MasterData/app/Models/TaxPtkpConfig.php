<?php

namespace Modules\MasterData\Models;

use App\Traits\HasDefaultRecord;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;

class TaxPtkpConfig extends Model
{
    use HasAutoCodeAndSlug;
    use HasDefaultRecord, HasModuleSchema;
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',   // TK/0, K/1 etc
        'name',   // Tidak Kawin 0 Tanggungan etc
        'tax_category', // A, B, C
        'annual_amount',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'annual_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
