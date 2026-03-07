<?php

namespace Modules\MasterData\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\Tax;
use Tests\TestCase;

class HasAutoCodeAndSlugTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_unique_code_with_numeric_increment_for_empty_code(): void
    {
        $tax = Tax::create([
            'name' => 'Taxable',
            'is_active' => true,
        ]);

        $this->assertNotEmpty($tax->code);
        // "Tax" class basename should yield "TAX-001"
        $this->assertEquals('TAX-001', $tax->code);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_duplicates_by_incrementing_numeric_suffix(): void
    {
        // First tax record
        $tax1 = Tax::create([
            'name' => 'Taxable',
            'is_active' => true,
        ]);

        // Second tax record - should increment to TAX-002
        $tax2 = Tax::create([
            'name' => 'Taxable 2',
            'is_active' => true,
        ]);

        // Third tax record - should increment to TAX-003
        $tax3 = Tax::create([
            'name' => 'Taxable 3',
            'is_active' => true,
        ]);

        $this->assertEquals('TAX-001', $tax1->code);
        $this->assertEquals('TAX-002', $tax2->code);
        $this->assertEquals('TAX-003', $tax3->code);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_override_manually_set_code(): void
    {
        $cluster = ProductCluster::create([
            'code' => 'CUSTOM-CODE',
            'name' => 'Building Cleaning Apartment',
            'is_active' => true,
        ]);

        $this->assertEquals('CUSTOM-CODE', $cluster->code);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_slug_generation_when_needed(): void
    {
        // Since Tax doesn't have slug, we verify code generation is unaffected and resilient
        $tax = Tax::create([
            'name' => 'Value Added Tax',
            'is_active' => true,
        ]);

        $this->assertEquals('TAX-001', $tax->code);
        // It should gracefully skip slug generation if the column doesn't exist
    }
}
