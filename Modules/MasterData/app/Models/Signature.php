<?php

namespace Modules\MasterData\Models;

use App\Models\User;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Modules\MasterData\Observers\SignatureObserver;

#[ObservedBy(SignatureObserver::class)]
class Signature extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'signable_type',
        'signable_id',
        'role',
        'signature_type',
        'signer_name',
        'signer_title',

        'ip_address',
        'user_agent',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signable(): MorphTo
    {
        return $this->morphTo();
    }
}
