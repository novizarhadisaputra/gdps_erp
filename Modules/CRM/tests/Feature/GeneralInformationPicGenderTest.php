<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\GeneralInformationPic;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Enums\Gender;
use Modules\MasterData\Models\ContactRole;
use Tests\TestCase;

class GeneralInformationPicGenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_gender_can_be_saved_and_cast_on_general_information_pic(): void
    {
        $customer = Customer::create(['name' => 'Test Customer']);
        $lead = Lead::create([
            'title' => 'Test Lead',
            'customer_id' => $customer->id,
            'status' => 'approach',
        ]);
        $gi = GeneralInformation::create([
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'scope_of_work' => 'Test SOW',
        ]);

        $contactRole = ContactRole::create([
            'name' => 'Test Role',
            'status' => \Modules\MasterData\Enums\ActiveStatus::Active,
        ]);

        $pic = GeneralInformationPic::create([
            'general_information_id' => $gi->id,
            'contact_role_id' => $contactRole->id,
            'gender' => Gender::Male,
            'name' => 'John Doe',
        ]);

        $this->assertEquals(Gender::Male, $pic->gender);

        $pic->update(['gender' => Gender::Female]);
        $this->assertEquals(Gender::Female, $pic->fresh()->gender);
    }
}
