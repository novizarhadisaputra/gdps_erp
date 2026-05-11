<?php

namespace Modules\MasterData\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\Tax;
use Tests\TestCase;

class TaxCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Standard Exclusive Tax (e.g. PPN 12%)
     */
    public function test_standard_exclusive_tax_calculation(): void
    {
        $tax = Tax::factory()->create([
            'calculation_type' => 'exclusive',
            'rate' => 12.00,
        ]);

        $amount = 1000000; // 1.000.000
        $expectedTax = 120000; // 12% of 1.000.000

        $this->assertEquals($expectedTax, $tax->calculateTax($amount));

        // Test with decimals (should floor)
        $amountWithDecimals = 168542000;
        $expectedTaxDecimals = 20225040; // 168.542.000 * 12% = 20.225.040
        $this->assertEquals($expectedTaxDecimals, $tax->calculateTax($amountWithDecimals));
    }

    /**
     * Test CoreTax Nilai Lain (Formula) - Effective 11% using 12% rate
     */
    public function test_coretax_nilai_lain_formula_calculation(): void
    {
        $tax = Tax::factory()->create([
            'calculation_type' => 'formula',
            'rate' => 12.00,
            'base_rate_numerator' => 11,
            'base_rate_denominator' => 12,
        ]);

        // Scenario: Laptop Rp 15.000.000
        // DPP = Floor(15.000.000 * 11/12) = 13.750.000
        // PPN = Floor(13.750.000 * 12%) = 1.650.000
        $amount = 15000000;
        $expectedTax = 1650000;

        $this->assertEquals($expectedTax, $tax->calculateTax($amount));

        // Scenario with tricky decimals
        // Amount: 168.542.000
        // DPP = Floor(168.542.000 * 11/12) = Floor(154.496.833,33) = 154.496.833
        // PPN = Floor(154.496.833 * 12%) = Floor(18.539.619,96) = 18.539.619
        $complexAmount = 168542000;
        $expectedComplexTax = 18539619;

        $this->assertEquals($expectedComplexTax, $tax->calculateTax($complexAmount));
    }

    /**
     * Test Inclusive Tax (Gross Up)
     */
    public function test_inclusive_gross_up_tax_calculation(): void
    {
        $tax = Tax::factory()->create([
            'calculation_type' => 'inclusive',
            'rate' => 12.00,
        ]);

        // If price is 112.000 and tax is 12%, then base is 100.000 and tax is 12.000
        $amount = 112000;
        $expectedTax = 12000;

        $this->assertEquals($expectedTax, $tax->calculateTax($amount));

        // Tricky decimals
        // 1.000.000 inclusive 12%
        // Tax = 1.000.000 * (12/112) = 107.142,85... -> Floor -> 107.142
        $amountDecimal = 1000000;
        $expectedTaxDecimal = 107142;

        $this->assertEquals($expectedTaxDecimal, $tax->calculateTax($amountDecimal));
    }

    /**
     * Test Edge Cases
     */
    public function test_tax_edge_cases(): void
    {
        $tax = Tax::factory()->create([
            'calculation_type' => 'exclusive',
            'rate' => 0,
        ]);

        // 0% rate
        $this->assertEquals(0, $tax->calculateTax(1000000));

        // 0 amount
        $tax->rate = 12.00;
        $this->assertEquals(0, $tax->calculateTax(0));

        // Null formula values (should fallback to 1/1)
        $tax->calculation_type = 'formula';
        $tax->base_rate_numerator = null;
        $tax->base_rate_denominator = null;
        $this->assertEquals(120000, $tax->calculateTax(1000000));
    }
}
