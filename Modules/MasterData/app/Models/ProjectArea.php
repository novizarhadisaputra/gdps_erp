<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\MasterData\Database\Factories\ProjectAreaFactory;

#[ObservedBy([\Modules\MasterData\Observers\MasterDataObserver::class])]
class ProjectArea extends Model
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

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): \Modules\MasterData\Database\Factories\ProjectAreaFactory
    {
        return \Modules\MasterData\Database\Factories\ProjectAreaFactory::new();
    }
}
