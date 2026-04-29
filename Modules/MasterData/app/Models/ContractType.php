<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\ContractTypeFactory;
use Modules\MasterData\Traits\HasDefaultRecord;

class ContractType extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected static function newFactory(): ContractTypeFactory
    {
        return ContractTypeFactory::new();
    }

    protected $fillable = [
        'code',
        'name',
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
