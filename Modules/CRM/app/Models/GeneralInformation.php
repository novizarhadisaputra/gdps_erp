<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Modules\CRM\Observers\GeneralInformationObserver;

#[ObservedBy([GeneralInformationObserver::class])]
class GeneralInformation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_id',
        'status',
        'pic_customer_name',
        'pic_customer_phone',
        'pic_finance_name',
        'pic_finance_phone',
        'pic_finance_email',
        'risk_management',
        'feasibility_study',
        'description',
        'remarks',
        'rr_submission_id',
    ];

    protected function casts(): array
    {
        return [
            'risk_management' => 'array',
            'feasibility_study' => 'array',
        ];
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\Customer::class);
    }

    public function profitabilityAnalyses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Finance\Models\ProfitabilityAnalysis::class);
    }
}
