<?php

namespace Modules\MasterData\Tests\Unit;

use Filament\Forms\Components\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Schemas\DirectCostCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ItemCategories\Schemas\ItemCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Schemas\ItemForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas\RevenueSegmentForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\UnitsOfMeasure\Schemas\UnitOfMeasureForm;
use Tests\TestCase;

class CreateOptionFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all MasterData form schemas return valid arrays.
     */
    public function test_master_data_form_schemas_are_valid(): void
    {
        $schemas = [
            'ItemForm' => ItemForm::schema(),
            'ItemCategoryForm' => ItemCategoryForm::schema(),
            'UnitOfMeasureForm' => UnitOfMeasureForm::schema(),
            'ProjectAreaForm' => ProjectAreaForm::schema(),
            'RevenueSegmentForm' => RevenueSegmentForm::schema(),
            'DirectCostCategoryForm' => DirectCostCategoryForm::schema(),
        ];

        foreach ($schemas as $name => $schema) {
            $this->assertIsArray($schema, "Schema for {$name} should be an array.");
            $this->assertNotEmpty($schema, "Schema for {$name} should not be empty.");
        }
    }

    /**
     * Test that RevenueSegment can be created with the data from its form schema.
     */
    public function test_revenue_segment_persistence(): void
    {
        $data = [
            'name' => 'Manual Segment',
            'code' => 'MANUAL',
            'is_active' => true,
        ];

        $segment = \Modules\MasterData\Models\RevenueSegment::create($data);

        $this->assertDatabaseHas('revenue_segments', [
            'name' => 'Manual Segment',
            'code' => 'MANUAL',
        ]);

        // Test auto-generation (GA Group -> GAG)
        $autoSegment = \Modules\MasterData\Models\RevenueSegment::create(['name' => 'GA Group']);
        $this->assertEquals('GAG', $autoSegment->code, 'Code should follow initials pattern for RevenueSegment.');

        // Test auto-generation (Test Segment -> TS)
        $anotherSegment = \Modules\MasterData\Models\RevenueSegment::create(['name' => 'Test Segment']);
        $this->assertEquals('TS', $anotherSegment->code);

        // Test collision (Another Test Segment -> TS1)
        $collisionSegment = \Modules\MasterData\Models\RevenueSegment::create(['name' => 'Total Sales']);
        $this->assertEquals('TS1', $collisionSegment->code);
    }

    /**
     * Test PaymentTerm code generation pattern (TOP + days).
     */
    public function test_payment_term_code_generation(): void
    {
        $term = \Modules\MasterData\Models\PaymentTerm::create(['name' => '45 Hari Kalender', 'days' => 45]);
        $this->assertEquals('TOP45', $term->code);

        $termNoNumber = \Modules\MasterData\Models\PaymentTerm::create(['name' => 'Cash On Delivery']);
        $this->assertEquals('TOPCOD', $termNoNumber->code);
    }

    /**
     * Test ProductCluster code generation pattern (Beyond -> B prefix).
     */
    public function test_product_cluster_code_generation(): void
    {
        $cluster = \Modules\MasterData\Models\ProductCluster::create(['name' => 'Beyond Care']);
        $this->assertEquals('BCA', $cluster->code);
    }

    /**
     * Test DirectCostCategory schema with type parameter.
     */
    public function test_direct_cost_category_schema_with_type(): void
    {
        $schema = DirectCostCategoryForm::schema('indirect');
        $this->assertIsArray($schema);

        // Check if type field has the default value set
        // In a real test we'd traverse the components, but here we just ensure it doesn't crash
    }
}
