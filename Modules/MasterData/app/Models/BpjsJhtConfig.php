<?php

namespace Modules\MasterData\Models;

use App\Traits\HasDefaultRecord;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BpjsJhtConfig extends Model
{
    use HasDefaultRecord, HasModuleSchema, HasUuids;

    protected $fillable = [
        'name',
        'employee_type',
        'calculation_method',
        'has_tier',
        'employer_rate',
        'employee_rate',
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

    public function tiers(): HasMany
    {
        return $this->hasMany(BpjsJhtTier::class, 'bpjs_jht_config_id');
    }
}
