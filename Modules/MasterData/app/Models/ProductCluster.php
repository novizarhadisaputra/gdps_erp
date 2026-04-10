<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\ProductClusterFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductCluster extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): ProductClusterFactory
    {
        return ProductClusterFactory::new();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->useDisk('s3')
            ->singleFile();
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstTemporaryUrl(now()->addMinutes(60), 'logo')
        );
    }
}
