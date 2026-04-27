<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use HasModuleSchema;

    protected $fillable = [
        'bank_name',
        'account_number',
        'account_name',
        'swift_code',
        'currency',
        'gl_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
