<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JhtConfigTier extends Model
{
    use HasModuleSchema, HasUuids;

    protected $fillable = [
        'jht_config_id',
        'min_income',
        'max_income',
        'employer_nominal',
        'employee_nominal',
    ];

    public function jhtConfig(): BelongsTo
    {
        return $this->belongsTo(JhtConfig::class);
    }
}
