<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Database\Factories\ProjectTypeFactory;
use Modules\MasterData\Traits\HasUnitScoping;

// use Modules\MasterData\Database\Factories\ProjectTypeFactory;

class ProjectType extends Model
{
    use HasFactory, HasUnitScoping, HasUuids;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_id',
        'code',
        'name',
        'is_active',
    ];

    protected static function newFactory(): ProjectTypeFactory
    {
        return ProjectTypeFactory::new();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
