<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CRM\Database\Factories\ContractFactory;
use Modules\CRM\Enums\ContractStatus;
use Modules\MasterData\Models\Customer;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'proposal_id',
        'contract_number',
        'expiry_date',
        'status',
        'reminder_status',
        'termination_reason',
    ];

    protected $casts = [
        'status' => ContractStatus::class,
        'expiry_date' => 'date',
    ];

    protected static function newFactory(): ContractFactory
    {
        return ContractFactory::new();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
