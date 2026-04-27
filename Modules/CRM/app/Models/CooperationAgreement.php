<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Observers\CooperationAgreementObserver;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(CooperationAgreementObserver::class)]
class CooperationAgreement extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    protected static function newFactory(): \Modules\CRM\Database\Factories\CooperationAgreementFactory
    {
        return \Modules\CRM\Database\Factories\CooperationAgreementFactory::new();
    }

    use HasModuleSchema;

    protected $fillable = [
        'number',
        'customer_id',
        'lead_id',
        'proposal_id',
        'agreement_date',
        'amount',
        'tax_percentage',
        'status',
        'items',
        'sequence_number',
        'year',
        'is_manual',
    ];

    protected $casts = [
        'agreement_date' => 'date',
        'items' => 'array',
        'amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'is_manual' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\Modules\Project\Models\Project::class, 'sourceable');
    }

    public function salesOrders(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(SalesOrder::class, 'sourceable');
    }
}
