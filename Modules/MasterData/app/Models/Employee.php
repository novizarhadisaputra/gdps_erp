<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\EmployeeFactory;
use Modules\MasterData\Observers\MasterDataObserver;
use Modules\MasterData\Traits\HasUnitScoping;

// use Modules\MasterData\Database\Factories\EmployeeFactory;

#[ObservedBy([MasterDataObserver::class])]
class Employee extends Model
{
    use HasFactory, HasUnitScoping, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_id',
        'code',
        'name',
        'email',
        'position',
        'department',
        'status',
    ];

    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }
}
