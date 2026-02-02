<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'resource_type',
        'criteria_field',
        'operator',
        'value',
        'approver_type',
        'approver_role',
        'approver_user_id',
        'approver_unit_id',
        'approver_position',
        'signature_type',
        'order',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'order' => 'integer',
        'is_active' => 'boolean',
        'approver_role' => 'array',
        'approver_user_id' => 'array',
        'approver_unit_id' => 'array',
        'approver_position' => 'array',
    ];
}
