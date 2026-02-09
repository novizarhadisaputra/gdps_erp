<?php

namespace Modules\CRM\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\SalesPlan;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\SkillCategory;

class SalesPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leads = Lead::all();
        $user = User::first();
        $revenueSegment = RevenueSegment::first();
        $productCluster = ProductCluster::first();
        $projectType = ProjectType::first();
        $skillCategory = SkillCategory::first();
        $industrialSector = IndustrialSector::first();
        $projectArea = ProjectArea::first();
        $jobPositions = JobPosition::pluck('id')->toArray();

        foreach ($leads as $lead) {
            SalesPlan::updateOrCreate(
                ['lead_id' => $lead->id],
                [
                    'ams_id' => $user?->id,
                    'revenue_segment_id' => $revenueSegment?->id,
                    'product_cluster_id' => $productCluster?->id,
                    'project_type_id' => $projectType?->id,
                    'skill_category_id' => $skillCategory?->id,
                    'industrial_sector_id' => $industrialSector?->id,
                    'project_area_id' => $projectArea?->id,
                    'job_positions' => array_slice($jobPositions, 0, 2),
                    'estimated_value' => rand(100000000, 1000000000),
                    'management_fee_percentage' => rand(5, 15),
                    'margin_percentage' => rand(10, 30),
                    'priority_level' => rand(1, 3),
                    'confidence_level' => 'moderate',
                    'start_date' => now()->addMonths(1),
                    'end_date' => now()->addMonths(13),
                    'top_days' => 30,
                ]
            );
        }
    }
}
