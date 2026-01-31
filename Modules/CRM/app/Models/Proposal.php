<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CRM\Database\Factories\ProposalFactory;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Lead;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\WorkScheme;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Modules\CRM\Observers\ProposalObserver;

#[ObservedBy(ProposalObserver::class)]
class Proposal extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'lead_id',
        'customer_id',
        'profitability_analysis_id',
        'work_scheme_id',
        'proposal_number',
        'amount',
        'status',
        'submission_date',
        'signatures',
    ];

    protected $casts = [
        'status' => ProposalStatus::class,
        'submission_date' => 'date',
        'signatures' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('final_proposal')
            ->useDisk('s3')
            ->singleFile();
    }

    protected static function newFactory(): ProposalFactory
    {
        return ProposalFactory::new();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workScheme(): BelongsTo
    {
        return $this->belongsTo(WorkScheme::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }
}
