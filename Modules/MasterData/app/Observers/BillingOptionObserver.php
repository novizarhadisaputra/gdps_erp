<?php

namespace Modules\MasterData\Observers;

use Illuminate\Support\Str;
use Modules\MasterData\Models\BillingOption;

class BillingOptionObserver
{
    public function saving(BillingOption $billingOption): void
    {
        if (empty($billingOption->code)) {
            $billingOption->code = $this->generateCode($billingOption->name);
        }
    }

    protected function generateCode(string $name): string
    {
        // For Billing Options, use uppercase slug or abbreviation
        $code = strtoupper(Str::slug($name, '_'));

        // If too long, try abbreviation
        if (strlen($code) > 6) {
            $words = explode(' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', $name));
            $code = '';
            foreach ($words as $word) {
                if (! empty($word)) {
                    $code .= strtoupper($word[0]);
                }
            }
        }

        // Ensure uniqueness
        $baseCode = $code;
        $counter = 1;
        while (BillingOption::where('code', $code)->exists()) {
            $code = $baseCode.'_'.$counter;
            $counter++;
        }

        return $code;
    }
}
