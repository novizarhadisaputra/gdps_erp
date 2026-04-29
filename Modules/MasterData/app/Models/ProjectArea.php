<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\ProjectAreaFactory;
use Modules\MasterData\Observers\ProjectAreaObserver;
use Modules\MasterData\Traits\HasDefaultRecord;

#[ObservedBy(ProjectAreaObserver::class)]
class ProjectArea extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'api_code',
        'province_id',
        'regency_id',
        'name',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): ProjectAreaFactory
    {
        return ProjectAreaFactory::new();
    }

    public function province(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }
}
