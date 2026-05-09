<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Finance\Enums\AccrueInvoiceMappingStatus;

class AccrueInvoiceMapping extends Model
{
    use HasFactory, HasModuleSchema, HasUuids, SoftDeletes;

    protected $fillable = [
        'accrue_revenue_item_id',
        'invoice_id',
        'allocated_amount',
        'reverse_amount',
        'reverse_journal_entry_id',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
            'reverse_amount' => 'decimal:2',
            'status' => AccrueInvoiceMappingStatus::class,
        ];
    }

    public function accrueRevenueItem(): BelongsTo
    {
        return $this->belongsTo(AccrueRevenueItem::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function reverseJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reverse_journal_entry_id');
    }
}
