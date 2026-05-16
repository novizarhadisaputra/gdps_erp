<?php

namespace Modules\Logistics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Logistics\Models\PurchaseRequest;
use Modules\Logistics\Models\PurchaseRequestItem;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\UnitOfMeasure;

class PurchaseRequestItemFactory extends Factory
{
    protected $model = PurchaseRequestItem::class;

    public function definition(): array
    {
        $estimatedPrice = $this->faker->randomFloat(2, 10000, 1000000);
        $quantity = $this->faker->numberBetween(1, 100);

        return [
            'purchase_request_id' => PurchaseRequest::factory(),
            'item_id' => Item::factory(),
            'quantity' => $quantity,
            'unit_of_measure_id' => UnitOfMeasure::factory(),
            'estimated_price' => $estimatedPrice,
            'total_estimated_price' => $estimatedPrice * $quantity,
        ];
    }
}
