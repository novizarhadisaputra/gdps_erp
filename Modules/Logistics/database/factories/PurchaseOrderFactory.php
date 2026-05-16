<?php

namespace Modules\Logistics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Logistics\Enums\PurchaseOrderStatus;
use Modules\Logistics\Models\PurchaseOrder;
use Modules\Logistics\Models\PurchaseRequest;
use Modules\Logistics\Models\Warehouse;
use Modules\MasterData\Models\Vendor;
use Modules\Project\Models\Project;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat(2, 1000000, 50000000);
        $taxAmount = $totalAmount * 0.11;

        return [
            'po_number' => 'PO/'.now()->format('Y/m').'/'.$this->faker->unique()->numerify('####'),
            'purchase_request_id' => PurchaseRequest::factory(),
            'vendor_id' => Vendor::factory(),
            'project_id' => Project::factory(),
            'total_amount' => $totalAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $totalAmount + $taxAmount,
            'warehouse_id' => Warehouse::factory(),
            'status' => PurchaseOrderStatus::Draft,
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
