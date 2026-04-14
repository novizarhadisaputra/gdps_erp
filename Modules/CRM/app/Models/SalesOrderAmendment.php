<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\SalesOrderAmendmentFactory;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;
use Modules\CRM\Observers\SalesOrderAmendmentObserver;

#[ObservedBy(SalesOrderAmendmentObserver::class)]
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
        'sequence_number',
        'year',
        'content_config',
    ];

    protected function casts(): array
    {
        return [
            'amendment_date' => 'date',
            'before_snapshot' => 'array',
            'after_snapshot' => 'array',
            'content_config' => 'array',
            'status' => SalesOrderAmendmentStatus::class,
        ];
    }

    protected static function newFactory(): SalesOrderAmendmentFactory
    {
        return SalesOrderAmendmentFactory::new();
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
