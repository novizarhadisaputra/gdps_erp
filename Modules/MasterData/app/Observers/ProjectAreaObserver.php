<?php

namespace Modules\MasterData\Observers;

use Modules\MasterData\Models\ProjectArea;

class ProjectAreaObserver
{
    /**
     * Handle the ProjectArea "creating" event.
     */
    public function creating(ProjectArea $projectArea): void
    {
        if (empty($projectArea->code)) {
            $projectArea->code = $this->generateCode($projectArea);
        }
    }

    /**
     * Handle the ProjectArea "updating" event.
     */
    public function updating(ProjectArea $projectArea): void
    {
        if (empty($projectArea->code)) {
            $projectArea->code = $this->generateCode($projectArea);
        }
    }

    /**
     * Generate a code based on the name or a fallback prefix.
     */
    protected function generateCode(ProjectArea $projectArea): string
    {
        $name = $projectArea->name;

        // If it's a known city/regency, try to get some initials
        $words = explode(' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', $name));
        $initials = '';
        foreach ($words as $word) {
            if (! empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }

        // Base code prefix: either initials (if >= 3 chars) or 'PAR'
        $prefix = (strlen($initials) >= 3) ? substr($initials, 0, 4) : 'PAR';

        // Ensure uniqueness
        $counter = 1;
        $finalCode = $prefix;

        // If the code is too short or common, add a number
        if (strlen($finalCode) < 3 || ProjectArea::where('code', $finalCode)->exists()) {
            $finalCode = $prefix.'-'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT);
            while (ProjectArea::where('code', $finalCode)->exists()) {
                $counter++;
                $finalCode = $prefix.'-'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT);
            }
        }

        return $finalCode;
    }
}
