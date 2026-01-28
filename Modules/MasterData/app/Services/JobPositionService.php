<?php

namespace Modules\MasterData\Services;

use Illuminate\Support\Facades\Http;

class JobPositionService
{
    /**
     * Get all job positions from 3rd party API.
     */
    public function getJobPositions(): array
    {
        // Mocking API call
        // In reality, this would be:
        // return Http::get('https://api.thirdparty.com/job-positions')->json();

        return [
            ['id' => 'jp001', 'name' => 'Project Manager'],
            ['id' => 'jp002', 'name' => 'Site Supervisor'],
            ['id' => 'jp003', 'name' => 'Field Technician'],
            ['id' => 'jp004', 'name' => 'Admin Support'],
            ['id' => 'jp005', 'name' => 'Safety Officer'],
        ];
    }

    /**
     * Get a single job position by ID.
     */
    public function getJobPositionById(string $id): ?array
    {
        return collect($this->getJobPositions())->firstWhere('id', $id);
    }
}
