<?php

namespace Modules\Finance\Services;

use Modules\CRM\Models\Customer;
use Modules\Finance\Models\AccountMapping;
use Modules\MasterData\Models\ProjectArea;

class AccrualMappingService
{
    /**
     * Resolve the GL Account for a specific mapping type using hierarchical lookup.
     * Order of priority:
     * 1. Specific Project Area
     * 2. Parent Project Area (recursive)
     * 3. Customer
     * 4. System Default (not implemented yet, fallback to null)
     */
    public function resolveAccount(string $type, ?ProjectArea $area = null, ?Customer $customer = null): ?string
    {
        // 1. Check Project Area
        if ($area) {
            $mapping = $this->lookupAreaMapping($type, $area);
            if ($mapping) {
                return $mapping;
            }
        }

        // 2. Check Customer
        if ($customer) {
            $mapping = $this->lookupCustomerMapping($type, $customer);
            if ($mapping) {
                return $mapping;
            }
        }

        return null;
    }

    protected function lookupAreaMapping(string $type, ProjectArea $area): ?string
    {
        $mapping = AccountMapping::where('mappable_id', $area->id)
            ->where('mappable_type', ProjectArea::class)
            ->where('type', $type)
            ->first();

        if ($mapping) {
            return $mapping->chart_of_account_id;
        }

        if ($area->parentable_id && $area->parentable_type === ProjectArea::class) {
            return $this->lookupAreaMapping($type, $area->parentable);
        }

        if ($area->parentable_id && $area->parentable_type === Customer::class) {
            return $this->lookupCustomerMapping($type, $area->parentable);
        }

        return null;
    }

    protected function lookupCustomerMapping(string $type, Customer $customer): ?string
    {
        $mapping = AccountMapping::where('mappable_id', $customer->id)
            ->where('mappable_type', Customer::class)
            ->where('type', $type)
            ->first();

        return $mapping?->chart_of_account_id;
    }
}
