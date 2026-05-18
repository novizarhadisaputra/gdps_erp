<?php

namespace Modules\MasterData\Models;

use App\Traits\HasDefaultRecord;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BpjsJkmConfig extends Model
{
    use HasDefaultRecord, HasModuleSchema, HasUuids;

    protected $fillable = [
        'name',
        'employee_type',
        'calculation_method',
        'has_tier',
        'tier_category',
        'employer_rate',
        'employee_rate',
        'employer_nominal',
        'employee_nominal',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'has_tier' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function tiers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BpjsTier::class, 'category', 'tier_category');
    }
}
