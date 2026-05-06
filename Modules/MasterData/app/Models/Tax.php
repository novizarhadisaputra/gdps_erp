<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\TaxFactory;
use Modules\MasterData\Traits\HasAutoCodeAndSlug;
use Modules\MasterData\Traits\HasDefaultRecord;

// use Modules\MasterData\Database\Factories\TaxFactory;

class Tax extends Model
{
    use HasAutoCodeAndSlug, HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'calculation_type',
        'rate',
        'base_rate_numerator',
        'base_rate_denominator',
        'is_active',
        'is_default',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'base_rate_numerator' => 'integer',
            'base_rate_denominator' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): TaxFactory
    {
        return TaxFactory::new();
    }

    public function calculateTax(float $amount): float
    {
        $numerator = (int) ($this->base_rate_numerator ?? 1);
        $denominator = (int) ($this->base_rate_denominator ?? 1);
        $rate = (float) ($this->rate ?? 0);

        // Standard calculation: Amount * Ratio * (Rate / 100)
        return round($amount * ($numerator / $denominator) * ($rate / 100));
    }

    public function getTaxLabelAttribute(): string
    {
        $label = $this->name;
        $num = (int) ($this->base_rate_numerator ?? 1);
        $den = (int) ($this->base_rate_denominator ?? 1);

        if ($num !== 1 || $den !== 1) {
            $label .= " (Adj. {$num}/{$den})";
        }

        return $label;
    }

    public static function getDefaultRate(string $category, float $fallback = 0): float
    {
        return (float) (self::where('category', $category)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()?->rate ?? $fallback);
    }
}
