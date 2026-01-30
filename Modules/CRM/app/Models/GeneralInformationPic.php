<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GeneralInformationPic extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'general_information_id',
        'contact_role_id',
        'name',
        'phone',
        'email',
    ];

    public function generalInformation(): BelongsTo
    {
        return $this->belongsTo(GeneralInformation::class);
    }

    public function contactRole(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\ContactRole::class);
    }
}
