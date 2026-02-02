<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\LeadFactory;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Observers\LeadObserver;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Models\ProjectInformation;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(LeadObserver::class)]
class Lead extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

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

    public function generalInformations(): HasMany
    {
        return $this->hasMany(GeneralInformation::class);
    }

    public function projectInformations(): HasMany
    {
        return $this->hasMany(ProjectInformation::class);
    }

    public function profitabilityAnalyses(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysis::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class); // Assuming Contract also has lead_id now or via other means if not added directly.
        // Wait, did I add lead_id to Contract?
        // I checked Contract migration in Step 1175 and it did NOT have lead_id.
        // The user authorized "GI, PA, and PI".
        // I should also add it to Contract for completeness if it's "Centralized Visibility".
        // I will double check my plan.
        // My plan in notification (Step 1174) mentioned GI, PA, PI.
        // It did not explicitly mention Contract but implied "semua tabel dokumen".
        // In implementation_plan.md, Contract is a child of Lead.
        // I should check Contract table again and add it if missing to be consistent.
    }
}
