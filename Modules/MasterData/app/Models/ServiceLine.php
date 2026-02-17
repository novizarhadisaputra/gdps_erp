<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\ServiceLineFactory;
use Modules\MasterData\Observers\MasterDataObserver;
use Modules\MasterData\Traits\HasUnitScoping;

#[ObservedBy(MasterDataObserver::class)]
class ServiceLine extends Model
{
    use HasFactory, HasUnitScoping, HasUuids;

    protected static function newFactory(): ServiceLineFactory
    {
        return ServiceLineFactory::new();
    }

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
