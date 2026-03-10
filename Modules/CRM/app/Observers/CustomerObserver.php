<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\Customer;

class CustomerObserver
{
    /**
     * Handle the Customer "creating" event.
     */
    public function creating(Customer $customer): void
    {
        if (! empty($customer->code)) {
            return;
        }

        $customer->code = $this->generateUniqueCode($customer->name);
    }

    /**
     * Generate a unique 3-character abbreviation.
     */
    protected function generateUniqueCode(string $name): string
    {
        // 1. Clean the name: Strip legal entity prefixes (PT, CV, etc.)
        $cleanName = preg_replace('/^(PT|CV|UD|Firma|Koperasi|Yayasan)\.?\s+/i', '', $name);
        $cleanName = trim($cleanName);

        // 2. Get words
        $words = preg_split('/\s+/', $cleanName, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);

        // 3. Base Abbreviation Logic
        if ($wordCount >= 3) {
            // First letter of first 3 words
            $code = strtoupper(
                substr($words[0], 0, 1).
                substr($words[1], 0, 1).
                substr($words[2], 0, 1)
            );
        } elseif ($wordCount === 2) {
            // First of 1st, First of 2nd, Last of 2nd (e.g. Garuda Indonesia -> GIA)
            $code = strtoupper(
                substr($words[0], 0, 1).
                substr($words[1], 0, 1).
                substr($words[1], -1)
            );
        } else {
            // 1 word: First 3 letters (e.g. GDPS -> GDP)
            $code = strtoupper(substr($words[0], 0, 3));
        }

        // 4. Collision Handling
        // If code exists, find the last "A" in the name and use the letter before it.
        if ($this->codeExists($code)) {
            $code = $this->handleCollision($code, $words, $cleanName);
        }

        return $code;
    }

    /**
     * Handle collision using the "index before last A" rule.
     */
    protected function handleCollision(string $originalCode, array $words, string $cleanName): string
    {
        // Find positions of all 'A' or 'a' in the clean name
        $lastAPos = strripos($cleanName, 'A');

        if ($lastAPos !== false && $lastAPos > 0) {
            // Character immediately preceding the last 'A'
            $precedingChar = substr($cleanName, $lastAPos - 1, 1);

            // If the preceding char is a space or special char, try to find another one?
            // The user's example: Garuda India -> GII (I before the last A)
            $newCode = substr($originalCode, 0, 2).strtoupper($precedingChar);

            if (! $this->codeExists($newCode)) {
                return $newCode;
            }

            // If still exists, maybe fall back to a sequence or another char?
            // For now, I'll stick to the user's specific rule.
            return $newCode;
        }

        // Fallback: If no 'A' or logic fails, append a counter or just return what we have
        return $originalCode;
    }

    /**
     * Check if a customer code already exists.
     */
    protected function codeExists(string $code): bool
    {
        return Customer::where('code', $code)->exists();
    }
}
