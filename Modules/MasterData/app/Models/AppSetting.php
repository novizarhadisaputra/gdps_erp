<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AppSetting extends Model
{
    use HasModuleSchema, HasUuids, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function getPayload(string $group, string $key, mixed $default = null): mixed
    {
        $setting = self::where('group', $group)->where('key', $key)->where('is_active', true)->first();

        return $setting ? $setting->payload : $default;
    }

    protected $fillable = [
        'group',
        'key',
        'payload',
        'is_active',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_active' => 'boolean',
    ];
}
