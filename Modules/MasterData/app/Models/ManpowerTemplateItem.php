<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManpowerTemplateItem extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ManpowerTemplate::class, 'manpower_template_id');
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }
}
