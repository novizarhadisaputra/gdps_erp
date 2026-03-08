<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\SalesPlan;
use Tests\TestCase;

class GeneralInformationStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;

    protected $lead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create(['name' => 'Test Customer']);
        $this->lead = Lead::create([
            'title' => 'Test Lead',
            'customer_id' => $this->customer->id,
            'status' => 'approach',
        ]);
    }

    public function test_gi_is_created_with_draft_status(): void
    {
        $gi = GeneralInformation::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'scope_of_work' => 'Test SOW',
            'status' => GeneralInformationStatus::Draft,
        ]);

        $this->assertEquals(GeneralInformationStatus::Draft, $gi->status);
    }

    public function test_sales_plan_to_gi_creates_draft_status(): void
    {
        $salesPlan = SalesPlan::create([
            'lead_id' => $this->lead->id,
        ]);

        $gi = $salesPlan->toGeneralInformation();

        $this->assertEquals(GeneralInformationStatus::Draft, $gi->status);
    }

    public function test_gi_status_enum_has_correct_attributes(): void
    {
        $this->assertEquals('Draft', GeneralInformationStatus::Draft->getLabel());
        $this->assertEquals('gray', GeneralInformationStatus::Draft->getColor());

        $this->assertEquals('Submitted', GeneralInformationStatus::Submitted->getLabel());
        $this->assertEquals('info', GeneralInformationStatus::Submitted->getColor());
    }

    public function test_is_locked_method_uses_enum_cases(): void
    {
        $gi = GeneralInformation::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'status' => GeneralInformationStatus::Draft,
        ]);

        $this->assertFalse($gi->isLocked());

        $gi->update(['status' => GeneralInformationStatus::Submitted]);
        $this->assertTrue($gi->isLocked());

        $gi->update(['status' => GeneralInformationStatus::Approved]);
        $this->assertTrue($gi->isLocked());

        $gi->update(['status' => GeneralInformationStatus::Rejected]);
        $this->assertFalse($gi->isLocked());
    }
}
