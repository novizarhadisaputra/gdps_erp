<?php

namespace Modules\MasterData\Observers;

use Modules\MasterData\Models\WorkScheme;

class WorkSchemeObserver
{
    public function saving(WorkScheme $workScheme): void
    {
        if (empty($workScheme->code)) {
            $workScheme->code = $this->generateCode($workScheme->name);
        }
    }

    protected function generateCode(string $name): string
    {
        // Extract numbers and initials (e.g. "5 Hari Kerja" -> 5HK)
        $words = explode(' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', $name));
        $code = '';

        foreach ($words as $word) {
            if (! empty($word)) {
                if (is_numeric($word)) {
                    $code .= $word;
                } else {
                    $code .= strtoupper($word[0]);
                }
            }
        }

        // Ensure uniqueness
        $baseCode = $code;
        $counter = 1;
        while (WorkScheme::where('code', $code)->exists()) {
            $code = $baseCode.$counter;
            $counter++;
        }

        return $code;
    }
}
