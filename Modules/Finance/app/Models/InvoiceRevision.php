<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class InvoiceRevision extends Model implements HasMedia
{
    use HasFactory, HasModuleSchema, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'invoice_id',
        'number',
        'sequence_number',
        'year',
        'snapshot',
        'reason',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
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
}
