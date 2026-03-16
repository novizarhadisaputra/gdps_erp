<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\BillingOptionFactory;

// use Modules\MasterData\Database\Factories\BillingOptionFactory;

class BillingOption extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected static function newFactory(): BillingOptionFactory
    {
        return BillingOptionFactory::new();
    }
}
