<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;

class GeneralInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leads = Lead::all();

        foreach ($leads as $index => $lead) {
            GeneralInformation::updateOrCreate(
                ['lead_id' => $lead->id],
                [
                    'customer_id' => $lead->customer_id,
                    'document_number' => 'GI-2025-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'status' => 'draft',
                    'scope_of_work' => 'General services and maintenance.',
                    'project_area_id' => \Modules\MasterData\Models\ProjectArea::first()?->id,
                    'location' => 'Jakarta/Tangerang',
                    'estimated_start_date' => now()->addDays(30),
                    'estimated_end_date' => now()->addDays(395),
                    'rr_document_number' => 'RR-2025-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'rr_submission_id' => 'SUB-'.uniqid(),
                    'rr_status' => 'approved',
                    'rr_payload' => [
                        'metadata' => [
                            'source' => 'Manual Seeder',
                            'timestamp' => now()->toIso8601String(),
                        ],
                        'risk_assessment' => [
                            'overall_risk' => 'low',
                            'items' => [
                                ['risk' => 'Market Volatility', 'severity' => 'low'],
                                ['risk' => 'Operational Downtime', 'severity' => 'medium'],
                            ],
                        ],
                        'approval_details' => [
                            'approved_by' => 'System Admin',
                            'valid_until' => now()->addYear()->toDateString(),
                        ],
                    ],
                ]
            );
        }
    }
}
