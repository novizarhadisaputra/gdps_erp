<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpjsJkmTier extends Model
{
    use HasModuleSchema;
    use HasUuids;

    protected $fillable = [
        'bpjs_jkm_config_id',
        'min_value',
        'max_value',
        'employer_nominal',
        'employee_nominal',
        'employer_rate',
        'employee_rate',
    ];

    protected function casts(): array
    {
        return [
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'employer_nominal' => 'decimal:2',
            'employee_nominal' => 'decimal:2',
            'employer_rate' => 'decimal:4',
            'employee_rate' => 'decimal:4',
        ];
    }

    public function bpjsJkmConfig(): BelongsTo
    {
        return $this->belongsTo(BpjsJkmConfig::class);
    }
}
