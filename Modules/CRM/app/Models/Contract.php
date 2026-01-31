<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CRM\Database\Factories\ContractFactory;
use Modules\CRM\Enums\ContractStatus;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Modules\CRM\Observers\ContractObserver;

#[ObservedBy(ContractObserver::class)]
class Contract extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'customer_id',
        'proposal_id',
        'contract_number',
        'expiry_date',
        'status',
        'reminder_status',
        'termination_reason',
        'signatures',
    ];

    protected $casts = [
        'status' => ContractStatus::class,
        'expiry_date' => 'date',
        'signatures' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('signed_contract')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('termination_evidence')
            ->useDisk('s3')
            ->singleFile();
    }

    protected static function newFactory(): ContractFactory
    {
        return ContractFactory::new();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function getAmountAttribute(): float
    {
        return $this->proposal?->amount ?? 0.0;
    }
}
