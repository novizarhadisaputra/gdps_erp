<?php

namespace Modules\MasterData\Observers;

use Modules\MasterData\Models\UnitOfMeasure;

class UnitOfMeasureObserver
{
    public function saving(UnitOfMeasure $unitOfMeasure): void
    {
        if (empty($unitOfMeasure->code)) {
            $unitOfMeasure->code = $this->generateCode($unitOfMeasure->name);
        }
    }

    protected function generateCode(string $name): string
    {
        $name = strtoupper($name);

        // Use full name if 3-4 chars, else take initials
        if (strlen($name) >= 3 && strlen($name) <= 4) {
            $code = $name;
        } else {
            $words = explode(' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', $name));
            $code = '';
            foreach ($words as $word) {
                if (! empty($word)) {
                    $code .= $word[0];
                }
            }
            if (strlen($code) < 3) {
                $code = substr($name, 0, 3);
            }
        }

        // Ensure uniqueness
        $baseCode = $code;
        $counter = 1;
        while (UnitOfMeasure::where('code', $code)->exists()) {
            $code = substr($baseCode, 0, 3).$counter;
            $counter++;
        }

        return $code;
    }
}
