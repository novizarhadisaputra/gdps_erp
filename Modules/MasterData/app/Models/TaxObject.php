<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasDefaultRecord;

// use Modules\MasterData\Database\Factories\TaxObjectFactory;

class TaxObject extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'is_taxable',
        'description',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    // protected static function newFactory(): TaxObjectFactory
    // {
    //     // return TaxObjectFactory::new();
    // }
}
