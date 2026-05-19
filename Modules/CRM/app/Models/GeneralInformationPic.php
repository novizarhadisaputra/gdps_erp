<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Enums\Gender;
use Modules\MasterData\Models\ContactRole;
use Modules\MasterData\Models\JobPosition;

class GeneralInformationPic extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected $fillable = [
        'general_information_id',
        'contact_role_id',
        'job_position_id',
        'gender',
        'name',
        'phone',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
        ];
    }

    public function generalInformation(): BelongsTo
    {
        return $this->belongsTo(GeneralInformation::class);
    }

    public function contactRole(): BelongsTo
    {
        return $this->belongsTo(ContactRole::class);
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }
}
