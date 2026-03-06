<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Enums\BpjsCategory;
use Modules\MasterData\Enums\BpjsType;
use Modules\MasterData\Enums\CalculationCapType;
use Modules\MasterData\Enums\CalculationFloorType;
use Modules\MasterData\Enums\RiskLevel;

class BpjsConfig extends Model
{
    use HasUuids;
    use HasModuleSchema;

    protected $fillable = [
        'name',
        'type',
        'category',
        'bpjs_basis_type_id',
        'employer_rate',
        'employee_rate',
        'floor_type',
        'floor_nominal',
        'cap_type',
        'cap_nominal',
        'risk_level',
        'is_active',
    ];

    protected $casts = [
        'type' => BpjsType::class,
        'category' => BpjsCategory::class,
        'employer_rate' => 'decimal:4',
        'employee_rate' => 'decimal:4',
        'floor_type' => CalculationFloorType::class,
        'floor_nominal' => 'decimal:2',
        'cap_type' => CalculationCapType::class,
        'cap_nominal' => 'decimal:2',
        'risk_level' => RiskLevel::class,
        'is_active' => 'boolean',
    ];

    public function bpjsBasisType(): BelongsTo
    {
        return $this->belongsTo(BpjsBasisType::class);
    }
}
