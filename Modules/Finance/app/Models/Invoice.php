<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\CommunicationLog;
use Modules\CRM\Models\Customer;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Observers\InvoiceObserver;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(InvoiceObserver::class)]
class Invoice extends Model implements HasMedia
{
    use HasDigitalSignatures;
    use HasFactory;
    use HasModuleSchema;
    use HasTranslations;
    use HasUuids;
    use InteractsWithMedia;
    use SoftDeletes;

    protected static function newFactory()
    {
        return \Modules\Finance\Database\Factories\InvoiceFactory::new();
    }

    public array $translatable = [
        'tax_wording',
        'items',
    ];

    protected $fillable = [
        'customer_id',
        'tax_id',
        'project_area_id',
        'number',
        'sequence_number',
        'revision_number',
        'previous_code',
        'year',
        'invoice_date',
        'due_date',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'payment_info',
        'items',
        'content_config',
        'invoice_type',
        'tax_percentage',
        'tax_basis',
        'tax_base_amount',
        'tax_wording',
        'sourceable_id',
        'sourceable_type',
        'bank_account_id',
        'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_base_amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'payment_info' => 'array',
            'items' => 'array',
            'content_config' => 'array',
            'snapshot' => 'array',
        ];
    }

    public function sourceable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function communicationLogs(): MorphMany
    {
        return $this->morphMany(CommunicationLog::class, 'emailable');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\BankAccount::class);
    }

    public function accrueRevenueItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccrueRevenueItem::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proof')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('signed_invoice')
            ->useDisk('s3')
            ->singleFile();
    }

    public function revisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceRevision::class);
    }

    public function workCompletionReport(): BelongsTo
    {
        return $this->belongsTo(\Modules\Project\Models\WorkCompletionReport::class, 'work_completion_report_id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }
}
