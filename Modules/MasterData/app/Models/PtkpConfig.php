<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtkpConfig extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected $fillable = [
        'code',   // TK/0, K/1 etc
        'name',   // Tidak Kawin 0 Tanggungan etc
        'annual_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'annual_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
