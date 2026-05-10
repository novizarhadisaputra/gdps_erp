<?php

namespace Modules\MasterData\Observers;

use Modules\MasterData\Models\PaymentTerm;

class PaymentTermObserver
{
    public function saving(PaymentTerm $paymentTerm): void
    {
        if (empty($paymentTerm->code)) {
            $paymentTerm->code = $this->generateCode($paymentTerm->name);
        }
    }

    protected function generateCode(string $name): string
    {
        // Extract numbers from name (e.g. "30 Hari" -> 30)
        preg_match('/\d+/', $name, $matches);
        $days = ! empty($matches[0]) ? $matches[0] : '';

        if (! empty($days)) {
            $code = 'TOP'.$days;
        } else {
            // Fallback to initials if no numbers
            $words = explode(' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', $name));
            $code = 'TOP';
            foreach ($words as $word) {
                if (! empty($word)) {
                    $code .= strtoupper($word[0]);
                }
            }
        }

        // Ensure uniqueness
        $baseCode = $code;
        $counter = 1;
        while (PaymentTerm::where('code', $code)->exists()) {
            $code = $baseCode.$counter;
            $counter++;
        }

        return $code;
    }
}
