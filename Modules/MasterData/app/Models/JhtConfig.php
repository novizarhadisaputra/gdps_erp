<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JhtConfig extends Model
{
    use HasModuleSchema, HasUuids;

    protected $fillable = [
        'name',
        'employee_type',
        'has_tier',
        'employer_rate',
        'employee_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'has_tier' => 'boolean',
        ];
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(JhtConfigTier::class);
    }
}
