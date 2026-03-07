<?php

namespace Modules\MasterData\Traits;

use Illuminate\Support\Str;

trait HasAutoCodeAndSlug
{
    /**
     * Boot the trait for a model.
     * Hooks into the creating event to generate 'code' and 'slug'.
     */
    protected static function bootHasAutoCodeAndSlug(): void
    {
        static::creating(function ($model) {
            $model->generateSlug();
            $model->generateCode();
        });

        static::updating(function ($model) {
            // Only update slug if name changed and slug is empty or needs refresh
            // But usually we don't change slugs once created to avoid breaking URLs.
            // We ensure it exists if some external process cleared it.
            if (empty($model->slug)) {
                $model->generateSlug();
            }
            if (empty($model->code)) {
                $model->generateCode();
            }
        });
    }

    /**
     * Generate a URL-friendly slug based on the model's name.
     */
    public function generateSlug(): void
    {
        // Only generate if 'slug' should be populated.
        // During tests, models are unguarded, so isFillable('slug') returns true even if it's not.
        // We strictly check the $fillable array.
        $fillable = $this->getFillable();
        $supportsSlug = in_array('slug', $fillable) || array_key_exists('slug', $this->getAttributes());

        if ($supportsSlug) {
            if (empty($this->slug) && ! empty($this->name)) {
                $this->slug = Str::slug($this->name);
            }
        }
    }

    /**
     * Generate a clean, auto-incrementing code like BEN-001, PRJ-002 based on Class name.
     */
    public function generateCode(): void
    {
        if (! empty($this->code)) {
            return; // Don't overwrite if manual code was provided
        }

        // Check if model supports code
        if (! $this->isFillable('code') && ! array_key_exists('code', $this->getAttributes())) {
            // As a fallback for tests where attributes might not be initialized yet but it is in fillable
            $fillable = $this->getFillable();
            if (empty($fillable) || ! in_array('code', $fillable)) {
                return;
            }
        }

        // Get the class basename, e.g., 'BenefitType'
        $className = class_basename(static::class);

        // Convert 'BenefitType' to 'BENEFIT_TYPE' and take the first 3 letters of the first word
        $words = explode('_', Str::snake($className));
        $prefix = strtoupper(substr($words[0], 0, 3));

        // If it's a multi-word class, maybe use first letter of each?
        if (count($words) > 1) {
            $prefix = strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1).substr(end($words), 0, 1));
            // Fallback if end() is the same as [1]
            if (count($words) === 2) {
                $prefix = strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 2));
            }
        }

        // Get the latest ID to auto-increment. Use simple LIKE and PHP processing to avoid raw SQL differences.
        $latestRecords = static::query()
            ->where('code', 'LIKE', $prefix.'-%')
            ->get();

        $nextNumber = 1;
        if ($latestRecords->isNotEmpty()) {
            $maxNumber = 0;
            foreach ($latestRecords as $record) {
                if (preg_match('/-(\d+)$/', (string) $record->code, $matches)) {
                    $num = intval($matches[1]);
                    if ($num > $maxNumber) {
                        $maxNumber = $num;
                    }
                }
            }
            $nextNumber = $maxNumber + 1;
        }

        // Format: PFX-001
        $this->code = $prefix.'-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

    }
}
