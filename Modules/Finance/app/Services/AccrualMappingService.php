<?php

namespace Modules\Finance\Services;

use Modules\CRM\Models\Customer;
use Modules\Finance\Models\AccountMapping;
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
        ?string $revenueSegmentId = null
    ): ?string {
        // 1. Check Project Area Hierarchy
        if ($area) {
            $mapping = $this->lookupAreaMapping($type, $area, $revenueTypeId, $revenueSegmentId);
            if ($mapping) {
                return $mapping;
            }
        }

        // 2. Check Customer
        if ($customer) {
            $mapping = $this->lookupCustomerMapping($type, $customer, $revenueTypeId, $revenueSegmentId);
            if ($mapping) {
                return $mapping;
            }
        }

        return null;
    }

    protected function lookupAreaMapping(string $type, ProjectArea $area, ?string $revenueTypeId, ?string $revenueSegmentId): ?string
    {
        $mapping = $this->findBestMapping(ProjectArea::class, $area->id, $type, $revenueTypeId, $revenueSegmentId);

        if ($mapping) {
            return $mapping;
        }

        // Recursive lookup for parent area
        if ($area->parentable_id && $area->parentable_type === ProjectArea::class) {
            return $this->lookupAreaMapping($type, $area->parentable, $revenueTypeId, $revenueSegmentId);
        }

        // Fallback to customer if area is attached to customer
        if ($area->parentable_id && $area->parentable_type === Customer::class) {
            return $this->lookupCustomerMapping($type, $area->parentable, $revenueTypeId, $revenueSegmentId);
        }

        return null;
    }

    protected function lookupCustomerMapping(string $type, Customer $customer, ?string $revenueTypeId, ?string $revenueSegmentId): ?string
    {
        return $this->findBestMapping(Customer::class, $customer->id, $type, $revenueTypeId, $revenueSegmentId);
    }

    protected function findBestMapping(string $mappableType, string $mappableId, string $type, ?string $revenueTypeId, ?string $revenueSegmentId): ?string
    {
        $query = AccountMapping::where('mappable_type', $mappableType)
            ->where('mappable_id', $mappableId)
            ->where('type', $type);

        $exact = (clone $query)
            ->with('chartOfAccount')
            ->where('revenue_type_id', $revenueTypeId)
            ->where('revenue_segment_id', $revenueSegmentId)
            ->first();

        if ($exact) {
            return $exact->chartOfAccount?->code;
        }

        // Try Revenue Type match
        if ($revenueTypeId) {
            $typeMatch = (clone $query)
                ->with('chartOfAccount')
                ->where('revenue_type_id', $revenueTypeId)
                ->whereNull('revenue_segment_id')
                ->first();
            if ($typeMatch) {
                return $typeMatch->chartOfAccount?->code;
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
                return $segmentMatch->chartOfAccount?->code;
            }
        }

        // Try general match for this area/customer and mapping type
        $mapping = (clone $query)
            ->with('chartOfAccount')
            ->whereNull('revenue_type_id')
            ->whereNull('revenue_segment_id')
            ->first();

        return $mapping?->chartOfAccount?->code;
    }
}
