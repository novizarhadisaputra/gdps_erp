<?php

namespace Modules\CRM\Models;

use App\Models\Comment;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProposalRevision extends Model implements HasMedia
{
    use HasFactory, HasModuleSchema, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'proposal_id',
        'number',
        'snapshot',
        'reason',
        'user_id',
        'sequence_number',
        'year',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('final_proposal')
            ->useDisk('s3');

        $this->addMediaCollection('signed_proposal')
            ->useDisk('s3');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
