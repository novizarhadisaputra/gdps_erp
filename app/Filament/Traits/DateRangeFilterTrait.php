<?php

namespace App\Filament\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait DateRangeFilterTrait
{
    public ?string $dateRange = '30days';

    public ?string $customStartDate = null;

    public ?string $customEndDate = null;

    protected function getDateRangeOptions(): array
    {
        return [
            'today' => 'Today',
            '7days' => 'Last 7 Days',
            '30days' => 'Last 30 Days',
            '90days' => 'Last 90 Days',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'this_quarter' => 'This Quarter',
            'last_quarter' => 'Last Quarter',
            'this_year' => 'This Year',
            'last_year' => 'Last Year',
            'custom' => 'Custom Range',
        ];
    }

    protected function getDateRangeFilter(): array
    {
        return match ($this->dateRange) {
            'today' => [
                'start' => Carbon::today(),
                'end' => Carbon::today()->endOfDay(),
            ],
            '7days' => [
                'start' => Carbon::today()->subDays(7),
                'end' => Carbon::now(),
            ],
            '30days' => [
                'start' => Carbon::today()->subDays(30),
                'end' => Carbon::now(),
            ],
            '90days' => [
                'start' => Carbon::today()->subDays(90),
                'end' => Carbon::now(),
            ],
            'this_month' => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth(),
            ],
            'last_month' => [
                'start' => Carbon::now()->subMonth()->startOfMonth(),
                'end' => Carbon::now()->subMonth()->endOfMonth(),
            ],
            'this_quarter' => [
                'start' => Carbon::now()->startOfQuarter(),
                'end' => Carbon::now()->endOfQuarter(),
            ],
            'last_quarter' => [
                'start' => Carbon::now()->subQuarter()->startOfQuarter(),
                'end' => Carbon::now()->subQuarter()->endOfQuarter(),
            ],
            'this_year' => [
                'start' => Carbon::now()->startOfYear(),
                'end' => Carbon::now()->endOfYear(),
            ],
            'last_year' => [
                'start' => Carbon::now()->subYear()->startOfYear(),
                'end' => Carbon::now()->subYear()->endOfYear(),
            ],
            'custom' => [
                'start' => $this->customStartDate ? Carbon::parse($this->customStartDate) : Carbon::today()->subDays(30),
                'end' => $this->customEndDate ? Carbon::parse($this->customEndDate) : Carbon::now(),
            ],
            default => [
                'start' => Carbon::today()->subDays(30),
                'end' => Carbon::now(),
            ],
        };
    }

    protected function applyDateRangeFilter(Builder $query, string $dateColumn = 'created_at'): Builder
    {
        $range = $this->getDateRangeFilter();

        return $query->whereBetween($dateColumn, [
            $range['start'],
            $range['end'],
        ]);
    }

    protected function getDateRangeLabel(): string
    {
        $range = $this->getDateRangeFilter();

        return $range['start']->format('M d, Y').' - '.$range['end']->format('M d, Y');
    }
}
