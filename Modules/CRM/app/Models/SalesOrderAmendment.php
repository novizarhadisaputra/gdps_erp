<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;

class SalesOrderAmendment extends Model
{
    use HasFactory, HasModuleSchema, HasUuids, SoftDeletes;

    protected $fillable = [
        'sales_order_id',
        'amendment_number',
        'amendment_date',
        'reason',
        'before_snapshot',
        'after_snapshot',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amendment_date' => 'date',
            'before_snapshot' => 'array',
            'after_snapshot' => 'array',
            'status' => SalesOrderAmendmentStatus::class,
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
