<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\CRM\Database\Factories\ProposalFactory;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'proposal_number',
        'amount',
        'status',
        'submission_date',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\Client::class);
    }

    public function contracts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
