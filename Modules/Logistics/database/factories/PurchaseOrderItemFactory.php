<?php

namespace Modules\Logistics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Logistics\Models\PurchaseOrder;
use Modules\Logistics\Models\PurchaseOrderItem;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\UnitOfMeasure;

class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 10000, 1000000);
        $quantity = $this->faker->numberBetween(1, 100);
        $totalPrice = $unitPrice * $quantity;
        $taxAmount = $totalPrice * 0.11;

        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'item_id' => Item::factory(),
            'quantity' => $quantity,
            'unit_of_measure_id' => UnitOfMeasure::factory(),
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'tax_amount' => $taxAmount,
            'grand_total' => $totalPrice + $taxAmount,
        ];
    }
}
