<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\ProjectTypeFactory;

// use Modules\MasterData\Database\Factories\ProjectTypeFactory;

class ProjectType extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected static function newFactory(): ProjectTypeFactory
    {
        return ProjectTypeFactory::new();
    }
}
