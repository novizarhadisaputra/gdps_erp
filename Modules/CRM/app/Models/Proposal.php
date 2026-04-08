<?php

namespace Modules\CRM\Models;

use App\Models\Comment;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\CRM\Database\Factories\ProposalFactory;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Observers\ProposalObserver;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\WorkScheme;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(ProposalObserver::class)]
class Proposal extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasUuids, InteractsWithMedia;
    use HasModuleSchema;

    protected $fillable = [
        'lead_id',
        'customer_id',
        'profitability_analysis_id',
        'work_scheme_id',
        'title',
        'proposal_number',
        'amount',
        'status',
        'is_manual',
        'submission_date',
        'revision_number',
        'previous_code',
        'sequence_number',
        'year',
        'is_imported',
        'import_source_id',
        'content_config',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
            'submission_date' => 'date',
            'revision_number' => 'integer',
            'is_manual' => 'boolean',
            'content_config' => 'array',
        ];
    }

    public function communicationLogs(): MorphMany
    {
        return $this->morphMany(CommunicationLog::class, 'emailable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('final_proposal')
            ->useDisk('s3')
            ->singleFile();
        $this->addMediaCollection('signed_proposal')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('digital_signature')
            ->useDisk('s3')
            ->singleFile();
    }

    protected static function newFactory(): ProposalFactory
    {
        return ProposalFactory::new();
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ProposalRevision::class);
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

    public function minutesOfAgreements(): HasMany
    {
        return $this->hasMany(MinutesOfAgreement::class);
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    public function isComplete(): bool
    {
        return ! empty($this->customer_id) &&
            ! empty($this->work_scheme_id) &&
            ! empty($this->amount) &&
            ! empty($this->submission_date) &&
            ! empty($this->profitability_analysis_id);
    }

    public function getProductClusterIdAttribute(): ?string
    {
        return $this->profitabilityAnalysis?->product_cluster_id ?? $this->lead?->product_cluster_id;
    }
}
