<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProposalRevision extends Model
{
    use HasFactory, HasUuids, HasModuleSchema;

    protected $fillable = [
        'proposal_id',
        'revision_number',
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

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
