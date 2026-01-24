<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\CRM\Database\Factories\ContractFactory;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'proposal_id',
        'contract_number',
        'expiry_date',
        'status',
        'reminder_status',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\Client::class);
    }

    public function proposal(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
