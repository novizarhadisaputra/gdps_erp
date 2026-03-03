<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages\EditGeneralInformation;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Models\ProjectArea;
use Tests\TestCase;

class GeneralInformationMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_upload_persists_to_s3(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::factory()->create();
        $projectArea = ProjectArea::factory()->create();

        $lead = Lead::factory()->create([
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
        ]);

        $gi = GeneralInformation::factory()->create([
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
        ]);

        // Mock the route for InteractsWithParentRecord
        $route = new \Illuminate\Routing\Route(['GET', 'HEAD'], 'crm/leads/{lead}/general-information/{record}/edit', [
            'as' => 'filament.admin.crm.resources.leads.general-informations.edit',
        ]);
        $route->bind(request());
        $route->setParameter('lead', (string) $lead->id);
        $route->setParameter('record', (string) $gi->id);
        request()->setRouteResolver(fn () => $route);

        $file = TemporaryUploadedFile::fake()->create('tor.pdf', 100);

        Livewire::test(EditGeneralInformation::class, [
            'lead' => $lead->id,
            'record' => $gi->id,
            'parentRecord' => $lead,
        ])
            ->set('data.tor', $file) // Pass single file instead of array
            ->call('save')
            ->assertHasNoErrors();

        $gi->refresh();

        $this->assertTrue($gi->hasMedia('tor'), 'Media collection "tor" is empty.');
        
        $media = $gi->getFirstMedia('tor');
        $this->assertNotNull($media, 'Media record was not created.');
        $this->assertEquals('s3', $media->disk, 'Media was not saved to S3 disk.');
    }
}
