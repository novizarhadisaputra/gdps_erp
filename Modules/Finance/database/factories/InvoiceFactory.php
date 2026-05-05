<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;

class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'customer_id' => \Modules\CRM\Models\Customer::factory(),
            'tax_id' => \Modules\MasterData\Models\Tax::factory(),
            'project_area_id' => \Modules\MasterData\Models\ProjectArea::factory(),
            'year' => (int) date('Y'),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'amount' => 1000000,
            'tax_amount' => 110000,
            'total_amount' => 1110000,
            'status' => InvoiceStatus::Draft,
            'tax_percentage' => 11,
            'items' => [
                [
                    'description' => $this->faker->sentence,
                    'quantity' => 1,
                    'unit_price' => 1000000,
                    'amount' => 1000000,
                ],
            ],
        ];
    }
}
