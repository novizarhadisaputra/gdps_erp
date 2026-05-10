<?php

namespace Modules\MasterData\Observers;

use Modules\MasterData\Models\ProductCluster;

class ProductClusterObserver
{
    public function saving(ProductCluster $productCluster): void
    {
        if (empty($productCluster->code)) {
            $productCluster->code = $this->generateCode($productCluster->name);
        }
    }

    protected function generateCode(string $name): string
    {
        $words = explode(' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', $name));
        $code = '';

        foreach ($words as $word) {
            if (! empty($word)) {
                $code .= strtoupper($word[0]);
            }
        }

        // Special handling for "Beyond ..." pattern in seeders (e.g. Beyond Care -> BCA)
        if (count($words) >= 2 && strtoupper($words[0]) === 'BEYOND') {
            $code = 'B'.strtoupper(substr($words[1], 0, 2));
        }

        // Ensure uniqueness
        $baseCode = $code;
        $counter = 1;
        while (ProductCluster::where('code', $code)->exists()) {
            $code = $baseCode.$counter;
            $counter++;
        }

        return $code;
    }
}
