<?php

namespace Modules\MasterData\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterDataObserver
{
    /**
     * Handle the "creating" event for Master Data models.
     * Automatically generates unique abbreviation if code is empty.
     */
    public function creating(Model $model): void
    {
        if (empty($model->code) && isset($model->name)) {
            $model->code = $this->generateUniqueAbbreviation($model);
        }
    }

    protected function generateUniqueAbbreviation(Model $model): string
    {
        $abbreviation = strtoupper($this->createAbbreviation($model->name));
        $tableName = $model->getTable();

        // Check if this code already exists
        $exists = DB::table($tableName)
            ->where('code', $abbreviation)
            ->when($model->exists, function ($query) use ($model) {
                return $query->where('id', '!=', $model->id);
            })
            ->exists();

        if (! $exists) {
            return $abbreviation;
        }

        // If duplicate exists, decrement the last character index from last word
        $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', '', $model->name);
        $words = array_filter(explode(' ', $cleanName));
        $words = array_values($words);
        $wordCount = count($words);

        if ($wordCount === 1) {
            // Single word: TA + last char, then TA + second-to-last char, etc.
            $word = $words[0];
            $base = strtoupper(substr($word, 0, 2));

            for ($i = strlen($word) - 1; $i >= 2; $i--) {
                $newAbbr = $base.strtoupper(substr($word, $i, 1));

                $exists = DB::table($tableName)
                    ->where('code', $newAbbr)
                    ->when($model->exists, function ($query) use ($model) {
                        return $query->where('id', '!=', $model->id);
                    })
                    ->exists();

                if (! $exists) {
                    return $newAbbr;
                }
            }
        } else {
            // Multiple words: decrement index from last word
            $lastWord = $words[$wordCount - 1];
            $base = strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));

            // Try from last character backwards
            for ($i = strlen($lastWord) - 2; $i >= 0; $i--) {
                $newAbbr = $base.strtoupper(substr($lastWord, $i, 1));

                $exists = DB::table($tableName)
                    ->where('code', $newAbbr)
                    ->when($model->exists, function ($query) use ($model) {
                        return $query->where('id', '!=', $model->id);
                    })
                    ->exists();

                if (! $exists) {
                    return $newAbbr;
                }
            }
        }

        // Fallback: return original
        return $abbreviation;
    }

    protected function createAbbreviation(string $name): string
    {
        // Remove special characters and split by space
        $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        $words = array_filter(explode(' ', $cleanName));

        if (empty($words)) {
            return '';
        }

        $words = array_values($words); // Re-index
        $wordCount = count($words);

        // Single word: take first 3 letters
        if ($wordCount === 1) {
            return substr($words[0], 0, 3);
        }

        // Multiple words: first letter from word 1 + first letter from word 2 + last letter from last word
        $abbr = substr($words[0], 0, 1); // First letter of first word
        $abbr .= substr($words[1], 0, 1); // First letter of second word

        // Third letter: last character of last word
        $lastWord = $words[$wordCount - 1];
        $abbr .= substr($lastWord, -1); // Last character

        return $abbr;
    }
}
