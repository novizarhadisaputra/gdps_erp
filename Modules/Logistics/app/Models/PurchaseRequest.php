<?php

namespace Modules\Logistics\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
// use Modules\Logistics\Database\Factories\PurchaseRequestFactory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\CommunicationLog;
use Modules\Logistics\Enums\PurchaseRequestStatus;
use Modules\Logistics\Observers\PurchaseRequestObserver;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Modules\Project\Models\Project;

#[ObservedBy(PurchaseRequestObserver::class)]
class PurchaseRequest extends Model
{
    use HasDigitalSignatures, HasFactory, HasModuleSchema, HasUuids, SoftDeletes;

    protected $table = 'purchase_requests';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pr_number',
        'project_id',
        'requester_id',
        'total_amount',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseRequestStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requester_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function communicationLogs(): MorphMany
    {
        return $this->morphMany(CommunicationLog::class, 'emailable');
    }
}
