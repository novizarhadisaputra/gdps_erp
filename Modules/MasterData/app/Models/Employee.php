<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasUnitScoping;

// use Modules\MasterData\Database\Factories\EmployeeFactory;

#[ObservedBy([\Modules\MasterData\Observers\MasterDataObserver::class])]
class Employee extends Model
{
    use HasFactory, HasUnitScoping;

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

    protected static function newFactory(): \Modules\MasterData\Database\Factories\EmployeeFactory
    {
        return \Modules\MasterData\Database\Factories\EmployeeFactory::new();
    }
}
