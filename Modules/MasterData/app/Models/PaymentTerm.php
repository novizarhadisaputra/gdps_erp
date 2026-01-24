<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\MasterData\Database\Factories\PaymentTermFactory;

#[ObservedBy([\Modules\MasterData\Observers\MasterDataObserver::class])]
class PaymentTerm extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected static function newFactory(): \Modules\MasterData\Database\Factories\PaymentTermFactory
    {
        return \Modules\MasterData\Database\Factories\PaymentTermFactory::new();
    }
}
