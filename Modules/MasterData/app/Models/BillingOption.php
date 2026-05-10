<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Modules\MasterData\Database\Factories\BillingOptionFactory;

use Modules\MasterData\Database\Factories\BillingOptionFactory;
use Modules\MasterData\Observers\BillingOptionObserver;
use Modules\MasterData\Traits\HasDefaultRecord;

#[ObservedBy(BillingOptionObserver::class)]
class BillingOption extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
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

    protected static function newFactory(): BillingOptionFactory
    {
        return BillingOptionFactory::new();
    }
}
