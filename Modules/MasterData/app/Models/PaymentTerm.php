<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\PaymentTermFactory;
use Modules\MasterData\Traits\HasDefaultRecord;

// use Modules\MasterData\Database\Factories\PaymentTermFactory;

class PaymentTerm extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'days',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): PaymentTermFactory
    {
        return PaymentTermFactory::new();
    }
}
