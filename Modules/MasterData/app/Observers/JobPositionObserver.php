<?php

namespace Modules\MasterData\Observers;

use Illuminate\Support\Str;
use Modules\MasterData\Models\JobPosition;

class JobPositionObserver
{
    /**
     * Handle the JobPosition "creating" event.
     */
    public function creating(JobPosition $jobPosition): void
    {
        if (empty($jobPosition->code)) {
            $jobPosition->code = $this->generateCode($jobPosition->name);
        }
    }

    /**
     * Handle the JobPosition "updating" event.
     */
    public function updating(JobPosition $jobPosition): void
    {
        if (empty($jobPosition->code)) {
            $jobPosition->code = $this->generateCode($jobPosition->name);
        }
    }

    /**
     * Generate a code based on the name.
     */
    protected function generateCode(string $name): string
    {
        // Simple abbreviation logic: first letter of each word, up to 4 chars
        $words = explode(' ', $name);
        $code = '';
        foreach ($words as $word) {
            $code .= strtoupper($word[0] ?? '');
        }

        $baseCode = Str::limit($code, 4, '');

        // Ensure uniqueness
        $finalCode = $baseCode;
        $counter = 1;
        while (JobPosition::where('code', $finalCode)->exists()) {
            $finalCode = $baseCode.$counter;
            $counter++;
        }

        return $finalCode;
    }
}
