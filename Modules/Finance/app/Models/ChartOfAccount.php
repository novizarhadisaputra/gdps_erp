<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\FilamentTree\Concern\ModelTree;

class ChartOfAccount extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use HasModuleSchema;
    use ModelTree;

    protected $fillable = [
        'code',
        'name',
        'account_type',
        'parent_id',
        'order',
        'accountable_id',
        'accountable_type',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function determineTitleColumnName(): string
    {
        return 'name';
    }

    public static function defaultParentKey()
    {
        return null;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get the parent accountable model (e.g., BankAccount, Vendor, etc.)
     */
    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }
}
