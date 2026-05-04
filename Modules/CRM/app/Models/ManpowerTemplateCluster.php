<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Models\ProductCluster;

class ManpowerTemplateCluster extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    protected $fillable = [
        'manpower_template_id',
        'product_cluster_id',
        'description',
        'jkn_category',
        'thr_billing_method',
        'compensation_billing_method',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ManpowerTemplate::class, 'manpower_template_id');
    }

    public function productCluster(): BelongsTo
    {
        return $this->belongsTo(ProductCluster::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManpowerTemplateItem::class, 'manpower_template_cluster_id');
    }
}
