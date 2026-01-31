<?php

namespace App\Traits;

trait HasAutoNumber
{
    /**
     * Generate the auto number.
     * 
     * @param string $column The target column for the formatted number (e.g., proposal_number)
     * @param string $prefix The prefix (e.g., PROP, GI, PI, PA)
     */
    protected function generateAutoNumber(string $column, string $prefix): void
    {
        $year = date('Y');
        $shortYear = date('y');
        
        // Find the latest sequence for this year
        // We assume the table has 'year' and 'sequence_number' columns
        $latestRecord = static::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latestRecord ? $latestRecord->sequence_number + 1 : 1;
        
        $this->year = $year;
        $this->sequence_number = $sequence;
        
        // Format: GDPS/UB/[PREFIX]-[SEQ]/[YY]
        // Example: GDPS/UB/PROP-001/26
        // Sequence is padded with 3 zeros
        $formattedNumber = sprintf('GDPS/UB/%s-%03d/%s', $prefix, $sequence, $shortYear);
        
        $this->{$column} = $formattedNumber;
    }
}
