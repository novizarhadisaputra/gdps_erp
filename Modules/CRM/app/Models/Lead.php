<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\LeadFactory;
use Modules\CRM\Enums\LeadStatus;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\WorkScheme;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Modules\CRM\Observers\LeadObserver;

#[ObservedBy(LeadObserver::class)]
class Lead extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'title',
        'customer_id',
        'work_scheme_id',
        'status',
        'estimated_amount',
        'probability',
        'expected_closing_date',
        'position',
        'description',
        'user_id',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
        'estimated_amount' => 'decimal:2',
        'probability' => 'integer',
        'expected_closing_date' => 'date',
    ];

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workScheme(): BelongsTo
    {
        return $this->belongsTo(WorkScheme::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }
}
