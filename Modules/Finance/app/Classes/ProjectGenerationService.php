<?php

namespace Modules\Finance\Classes;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\AppSetting;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\Tax;
use Modules\Project\Models\Project;

class ProjectGenerationService
{
    public function generateFromPA(ProfitabilityAnalysis $pa): Project
    {
        return DB::transaction(function () use ($pa) {
            // 0. Get the source document (PO, WO, or CA) from the lead
            $source = $pa->lead?->cooperationAgreements()->latest()->first()
                ?? $pa->lead?->purchaseOrders()->latest()->first()
                ?? $pa->lead?->workOrders()->latest()->first();

            // 1. Calculate next sequence number for this Customer + Work Scheme
            $nextNumber = $this->getNextSequenceNumber($pa, $source);

            // 2. Validate required fields for Project Code generation to avoid 'XXX' or 'XX'
            $this->validateProjectCodeSegments($pa);

            // 3. Get Project Type from PA or Lead
            $projectTypeId = $pa->project_type_id;
            if (! $projectTypeId && $pa->work_scheme_id) {
                $projectTypeId = $pa->workScheme?->project_type_id;
            }
            $projectTypeId = $projectTypeId ?? $pa->lead?->project_type_id;

            // 3. Create or Update the Project
            $projectData = [
                'name' => $pa->proposal?->number ?? 'Project for '.$pa->customer?->name,
                'customer_id' => $pa->customer_id,
                'work_scheme_id' => $pa->work_scheme_id,
                'product_cluster_id' => $pa->product_cluster_id,
                'tax_id' => $pa->tax_id,
                'project_area_id' => $pa->project_area_id,
                'project_type_id' => $projectTypeId,
                'project_number' => $nextNumber,
                'status' => 'planning',
                'proposal_id' => $pa->proposal_id,
                'profitability_analysis_id' => $pa->id,
                'lead_id' => $pa->lead_id,
                'oprep_id' => $this->getEmployeeIdFromUser($pa->lead?->pic_costing_id),
                'ams_id' => $this->getEmployeeIdFromUser($pa->lead?->user_id),
                'sourceable_id' => $source?->id,
                'sourceable_type' => $source ? get_class($source) : null,
                'payment_term_id' => $pa->lead?->payment_term_id,
                'billing_option_id' => $pa->lead?->billing_option_id,
                'start_date' => $pa->lead?->start_date,
                'end_date' => $pa->lead?->end_date,
                'revenue_segment_id' => $pa->revenue_segment_id,
            ];

            // PIVOT SHIFT: Lookup by contract_id primary, fallback to lead_id or PA
            // We want to avoid creating duplicate projects if one already exists for the lead
            /** @var Project|null $project */
            $project = Project::query()
                ->when($source, fn ($q) => $q->where('sourceable_id', $source->id)->where('sourceable_type', get_class($source)))
                ->when(! $source && $pa->lead_id, fn ($q) => $q->where('lead_id', $pa->lead_id))
                ->when(! $source && ! $pa->lead_id, fn ($q) => $q->where('profitability_analysis_id', $pa->id))
                ->first();

            if ($project) {
                $project->update($projectData);
            } else {
                // Double check if there's any project with lead_id if we are currently looking by contract but found none
                if ($source && $pa->lead_id) {
                    /** @var Project|null $project */
                    $project = Project::where('lead_id', $pa->lead_id)->first();
                    if ($project) {
                        $project->update($projectData);
                    }
                }

                if (! $project) {
                    $project = Project::create($projectData);
                }
            }

            // 4. Populate Project Information from PA
            $details = $pa->analysis_details;

            $project->information()->updateOrCreate([], [
                'revenue_per_month' => $pa->revenue_per_month,
                'direct_cost' => $pa->direct_cost,
                'management_fee_per_month' => $pa->management_fee,
                'ppn_percentage' => $pa->tax?->percentage
                    ?? Tax::getDefaultRate('sales', AppSetting::getPayload('finance', 'global_financial_parameters')['vat_rate'] ?? 11.00),
                'start_date' => $pa->start_date ?? $pa->lead?->start_date,
                'end_date' => $pa->end_date ?? $pa->lead?->end_date,
                'analysis_details' => $details,
                'remuneration_details' => $pa->manpower_requirements,
                'lead_id' => $pa->lead_id,
                'oprep_id' => $projectData['oprep_id'],
                'ams_id' => $projectData['ams_id'],
                'project_type_id' => $projectData['project_type_id'],
                'payment_term_id' => $pa->lead?->payment_term_id,
                'billing_option_id' => $pa->lead?->billing_option_id,
                'description' => $pa->lead?->generalInformations()
                    ->whereIn('status', ['approved', 'submitted'])
                    ->latest('created_at')
                    ->first()?->scope_of_work ?? $pa->lead?->description,
            ]);

            // 5. Update PA status
            $pa->update([
                'status' => 'converted',
                'project_number' => $nextNumber,
            ]);

            // Force code update if parameters changed
            $project->number = Project::generateProjectNumber($project);
            $project->save();

            return $project;
        });
    }

    protected function validateProjectCodeSegments(ProfitabilityAnalysis $pa): void
    {
        $missing = [];

        if (! $pa->customer_id && ! $pa->lead?->customer_id) {
            $missing[] = 'Customer';
        }

        if (! $pa->project_area_id && ! $pa->lead?->project_area_id) {
            $missing[] = 'Project Area';
        }

        if (! $pa->product_cluster_id && ! $pa->lead?->product_cluster_id) {
            $missing[] = 'Product Cluster';
        }

        if (! $pa->tax_id && ! $pa->lead?->tax_id) {
            $missing[] = 'Tax Rate';
        }

        if (count($missing) > 0) {
            $fields = implode(', ', $missing);
            throw new \RuntimeException("Cannot generate Project: Missing required data for Project Code: {$fields}. Please ensure these are selected in the Profitability Analysis or Lead.");
        }

        // Also check if the codes are actually set on these models
        $pa->loadMissing(['customer', 'projectArea', 'productCluster', 'tax', 'lead.customer']);

        $customerCode = $pa->customer?->code ?? $pa->lead?->customer?->code;
        if (empty($customerCode)) {
            $missing[] = 'Customer Code';
        }

        $areaCode = $pa->projectArea?->code ?? $pa->lead?->projectArea?->code;
        if (empty($areaCode)) {
            $missing[] = 'Project Area Code';
        }

        $clusterCode = $pa->productCluster?->code ?? $pa->lead?->productCluster?->code;
        if (empty($clusterCode)) {
            $missing[] = 'Product Cluster Code';
        }

        $taxCode = $pa->tax?->code;
        if (empty($taxCode)) {
            $missing[] = 'Tax Code';
        }

        if (count($missing) > 0) {
            $fields = implode(', ', $missing);
            throw new \RuntimeException("Cannot generate Project: Missing codes in Master Data: {$fields}. Please check your Master Data configuration.");
        }
    }

    protected function getNextSequenceNumber(ProfitabilityAnalysis $pa, $source = null): int
    {
        // 1. Reuse project number from existing Project tied to this contract/lead
        /** @var Project|null $existingProject */
        $existingProject = null;
        if ($source) {
            $existingProject = Project::where('sourceable_id', $source->id)
                ->where('sourceable_type', get_class($source))
                ->first();
            // Fallback to lead if contract has no project yet but lead does
            if (! $existingProject && $pa->lead_id) {
                $existingProject = Project::where('lead_id', $pa->lead_id)->first();
            }
        } elseif ($pa->lead_id) {
            $existingProject = Project::where('lead_id', $pa->lead_id)->first();
        }

        if ($existingProject && ! empty($existingProject->project_number)) {
            return (int) $existingProject->project_number;
        }

        // 2. Reuse project number from PA if already assigned
        if (! empty($pa->project_number)) {
            return (int) $pa->project_number;
        }

        // 3. Shared Sequence Logic (Tax Variants)
        // If there's an existing project with the same Customer, Area, Cluster, and Work Scheme,
        // but a DIFFERENT Tax ID, it's considered a tax variant and should share the same sequence number.
        $sharedProject = Project::query()
            ->where('customer_id', $pa->customer_id)
            ->where('project_area_id', $pa->project_area_id)
            ->where('product_cluster_id', $pa->product_cluster_id)
            ->where('work_scheme_id', $pa->work_scheme_id)
            ->where('tax_id', '!=', $pa->tax_id)
            ->first();

        if ($sharedProject && ! empty($sharedProject->project_number)) {
            return (int) $sharedProject->project_number;
        }

        // 4. Fallback: Calculate next sequence number for this Customer
        // The sequence is scoped to the Customer to maintain a clean numbering plan.
        $lastProject = Project::where('customer_id', '=', $pa->customer_id)
            ->whereNotNull('project_number')
            ->orderByRaw('CAST(project_number AS INTEGER) DESC')
            ->first();

        return $lastProject ? (int) $lastProject->project_number + 1 : 1;
    }

    protected function getEmployeeIdFromUser(?string $userId): ?string
    {
        if (! $userId) {
            return null;
        }

        $user = User::find($userId);
        if (! $user || empty($user->email)) {
            return null;
        }

        return Employee::where('email', $user->email)->first()?->id;
    }
}
