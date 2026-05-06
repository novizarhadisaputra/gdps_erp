<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\CRM\Models\Customer;
use Modules\Finance\Models\AccountMapping;
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
            'has_branches' => 'boolean',
        ];
    }

    protected static function newFactory(): ProjectAreaFactory
    {
        return ProjectAreaFactory::new();
    }

    public function customers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Customer::class, config('database.default') === 'sqlite' ? 'customer_project_area' : 'crm.customer_project_area');
    }

    public function parentable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(self::class, 'parentable');
    }

    public function province(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function accountMappings(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AccountMapping::class, 'mappable');
    }

    /**
     * Get the root customer for this project area by traversing up the parentable hierarchy.
     */
    public function getCustomer(): ?\Modules\CRM\Models\Customer
    {
        if ($this->parentable_type === self::class && $this->parentable) {
            return $this->parentable->getCustomer();
        }

        return $this->customers()->first();
    }

    /**
     * Get all descendant project areas for a given parentable or customer.
     */
    public static function getAllDescendantsFor(\Illuminate\Database\Eloquent\Model $parentable): \Illuminate\Support\Collection
    {
        if ($parentable instanceof \Modules\CRM\Models\Customer) {
            $rootAreas = $parentable->projectAreas()->get();
            $all = collect($rootAreas);

            foreach ($rootAreas as $area) {
                $all = $all->merge(self::getAllDescendantsFor($area));
            }

            return $all->unique('id');
        }

        $areas = self::where('parentable_id', $parentable->id)
            ->where('parentable_type', get_class($parentable))
            ->get();

        $all = collect($areas);

        foreach ($areas as $area) {
            $all = $all->merge(self::getAllDescendantsFor($area));
        }

        return $all;
    }
}
