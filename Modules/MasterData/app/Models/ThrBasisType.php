<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;
use Modules\MasterData\Traits\HasDefaultRecord;

class ThrBasisType extends Model
{
    use HasAutoCodeAndSlug, HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'formula_code', // gaji_pokok | gaji_plus_tetap | gaji_plus_tetap_plus_sebagian
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
