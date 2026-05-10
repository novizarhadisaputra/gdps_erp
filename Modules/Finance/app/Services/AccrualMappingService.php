<?php

namespace Modules\Finance\Services;

use Modules\CRM\Models\Customer;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\AccrueRevenue;
use Modules\MasterData\Models\ProjectArea;

class AccrualMappingService
{
    /**
     * Resolve the GL Account for a specific mapping type using hierarchical lookup.
     * Dimensions considered: Area/Customer, Revenue Type, Revenue Segment.
     */
    public function resolveAccount(
        string $type,
        ?ProjectArea $area = null,
        ?Customer $customer = null,
        ?string $revenueTypeId = null,
        ?string $revenueSegmentId = null,
        ?string $taxId = null
    ): ?string {
        $mapping = $this->resolveAccountMapping($type, $area, $customer, $revenueTypeId, $revenueSegmentId, $taxId);

        return $mapping?->chartOfAccount?->code;
    }

    /**
     * Resolve the AccountMapping record using hierarchical lookup.
     */
    public function resolveAccountMapping(
        string $type,
        ?ProjectArea $area = null,
        ?Customer $customer = null,
        ?string $revenueTypeId = null,
        ?string $revenueSegmentId = null,
        ?string $taxId = null
    ): ?AccountMapping {
        // 1. Check Project Area Hierarchy
        if ($area) {
            $mapping = $this->lookupAreaMappingRecord($type, $area, $revenueTypeId, $revenueSegmentId, $taxId);
            if ($mapping) {
                return $mapping;
            }
        }

        // 2. Check Customer
        if ($customer) {
            $mapping = $this->lookupCustomerMappingRecord($type, $customer, $revenueTypeId, $revenueSegmentId);
            if ($mapping) {
                return $mapping;
            }
        }

        return null;
    }

    /**
     * Identify missing mappings for an Invoice record.
     */
    public function getMissingInvoiceMappings(\Modules\Finance\Models\Invoice $record): array
    {
        $missing = [];
        $projectArea = $record->projectArea;
        $customer = $record->customer;

        $arAccount = $this->resolveAccount(
            'receivable',
            $projectArea,
            $customer
        );

        $accrualAccount = $this->resolveAccount(
            'accrual',
            $projectArea,
            $customer
        );

        if (! $arAccount || ! $accrualAccount) {
            $missing[] = [
                'type' => 'invoice',
                'missing_receivable' => ! $arAccount,
                'missing_accrual' => ! $accrualAccount,
                'mappable_type' => $projectArea ? ProjectArea::class : Customer::class,
                'mappable_id' => $projectArea ? $projectArea->id : $customer->id,
            ];
        }

        return $missing;
    }

    public function getMissingMappings(AccrueRevenue $record): array
    {
        $missing = [];
        $projectArea = $record->projectArea;
        $customer = $record->customer;
        $revenueSegmentId = $record->project?->revenue_segment_id;

        foreach ($record->items as $item) {
            $accrualAccount = $this->resolveAccount(
                'accrual',
                $projectArea,
                $customer,
                $item->revenue_type_id,
                $revenueSegmentId
            );

            $revenueAccount = $this->resolveAccount(
                'revenue',
                $projectArea,
                $customer,
                $item->revenue_type_id,
                $revenueSegmentId
            );

            if (! $accrualAccount || ! $revenueAccount) {
                $missing[] = [
                    'item_id' => $item->id,
                    'revenue_type_id' => $item->revenue_type_id,
                    'revenue_type_name' => $item->revenueType?->name ?? 'Unknown',
                    'revenue_segment_id' => $revenueSegmentId,
                    'missing_accrual' => ! $accrualAccount,
                    'missing_revenue' => ! $revenueAccount,
                    'mappable_type' => $projectArea ? ProjectArea::class : Customer::class,
                    'mappable_id' => $projectArea ? $projectArea->id : $customer->id,
                ];
            }
        }

        return $missing;
    }

    protected function lookupAreaMappingRecord(string $type, ProjectArea $area, ?string $revenueTypeId, ?string $revenueSegmentId, ?string $taxId = null): ?AccountMapping
    {
        $mapping = $this->findBestMapping($area::class, $area->id, $type, $revenueTypeId, $revenueSegmentId, $taxId);

        if ($mapping) {
            return $mapping;
        }

        // Recursive lookup for parent area
        if ($area->parentable_id && $area->parentable_type === ProjectArea::class) {
            return $this->lookupAreaMappingRecord($type, $area->parentable, $revenueTypeId, $revenueSegmentId, $taxId);
        }

        // Fallback to customer if area is attached to customer
        if ($area->parentable_id && $area->parentable_type === Customer::class) {
            return $this->lookupCustomerMappingRecord($type, $area->parentable, $revenueTypeId, $revenueSegmentId, $taxId);
        }

        return null;
    }

    protected function lookupCustomerMappingRecord(string $type, Customer $customer, ?string $revenueTypeId, ?string $revenueSegmentId, ?string $taxId = null): ?AccountMapping
    {
        return $this->findBestMapping($customer::class, $customer->id, $type, $revenueTypeId, $revenueSegmentId, $taxId);
    }

    protected function findBestMapping(string $mappableType, string $mappableId, string $type, ?string $revenueTypeId, ?string $revenueSegmentId, ?string $taxId = null): ?AccountMapping
    {
        $query = AccountMapping::where('mappable_type', $mappableType)
            ->where('mappable_id', $mappableId)
            ->where('type', $type);

        // Priority 1: Exact match with tax_id (for withholding/specific taxes)
        if ($taxId) {
            $taxExact = (clone $query)
                ->with('chartOfAccount')
                ->where('tax_id', $taxId)
                ->first();
            if ($taxExact) {
                return $taxExact;
            }
        }

        // Priority 2: Exact match with Revenue Type and Segment
        $exact = (clone $query)
            ->with('chartOfAccount')
            ->where('revenue_type_id', $revenueTypeId)
            ->where('revenue_segment_id', $revenueSegmentId)
            ->whereNull('tax_id')
            ->first();

        if ($exact) {
            return $exact;
        }

        // Try Revenue Type match
        if ($revenueTypeId) {
            $typeMatch = (clone $query)
                ->with('chartOfAccount')
                ->where('revenue_type_id', $revenueTypeId)
                ->whereNull('revenue_segment_id')
                ->first();
            if ($typeMatch) {
                return $typeMatch;
            }
        }

        // Try Revenue Segment match
        if ($revenueSegmentId) {
            $segmentMatch = (clone $query)
                ->with('chartOfAccount')
                ->whereNull('revenue_type_id')
                ->where('revenue_segment_id', $revenueSegmentId)
                ->first();
            if ($segmentMatch) {
                return $segmentMatch;
            }
        }

        // Try general match for this area/customer and mapping type
        return (clone $query)
            ->with('chartOfAccount')
            ->whereNull('revenue_type_id')
            ->whereNull('revenue_segment_id')
            ->first();
    }
}
