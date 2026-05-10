<?php

namespace Modules\MasterData\Observers;

use Modules\MasterData\Models\RevenueSegment;

class RevenueSegmentObserver
{
    public function saving(RevenueSegment $revenueSegment): void
    {
        if (empty($revenueSegment->code)) {
            $revenueSegment->code = $this->generateCode($revenueSegment->name);
        }
    }

    protected function generateCode(string $name): string
    {
        $words = explode(' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', $name));
        $code = '';

        if (count($words) >= 2) {
            // Pattern like "GA Group" -> GAG (2 chars from 1st word, 1 from 2nd)
            if (strlen($words[0]) <= 2) {
                $code = strtoupper($words[0].substr($words[1], 0, 1));
            } else {
                $code = strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
            }
        } else {
            $code = strtoupper(substr($name, 0, 3));
        }

        // Ensure uniqueness by adding number if exists
        $baseCode = $code;
        $counter = 1;
        while (RevenueSegment::where('code', $code)->exists()) {
            $code = $baseCode.$counter;
            $counter++;
        }

        return $code;
    }
}
