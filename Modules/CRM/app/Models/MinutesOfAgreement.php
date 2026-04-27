<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CRM\Enums\MoAStatus;
use Modules\CRM\Observers\MinutesOfAgreementObserver;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(MinutesOfAgreementObserver::class)]
class MinutesOfAgreement extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasUuids, InteractsWithMedia;
    use HasModuleSchema;
    use HasTranslations;

    public array $translatable = [
        'scope_of_work',
        'timeline',
        'terms',
        'notes',
    ];

    protected $fillable = [
        'lead_id',
        'proposal_id',
        'customer_id',
        'number',
        'amount',
        'status',
        'negotiation_date',
        'notes',
        'scope_of_work',
        'timeline',
        'terms',
        'is_manual',
    ];

    protected function casts(): array
    {
        return [
            'status' => MoAStatus::class,
            'negotiation_date' => 'date',
            'amount' => 'decimal:2',
            'is_manual' => 'boolean',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('minutes_of_agreement')
            ->useDisk('s3')
            ->singleFile();
    }

    public function isComplete(): bool
    {
        return ! empty($this->customer_id) &&
            ! empty($this->proposal_id) &&
            ! empty($this->amount) &&
            ! empty($this->negotiation_date) &&
            ! empty($this->scope_of_work) &&
            ! empty($this->timeline) &&
            ! empty($this->terms);
    }
}
