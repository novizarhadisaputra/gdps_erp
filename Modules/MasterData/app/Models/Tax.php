<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\TaxFactory;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;

// use Modules\MasterData\Database\Factories\TaxFactory;

class Tax extends Model
{
    use HasAutoCodeAndSlug, HasFactory, HasUuids;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): TaxFactory
    {
        return TaxFactory::new();
    }
}
