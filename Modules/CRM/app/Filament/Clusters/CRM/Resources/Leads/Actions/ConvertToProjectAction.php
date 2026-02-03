<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BillingOptions\Schemas\BillingOptionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Schemas\EmployeeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas\PaymentTermForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Schemas\ProjectTypeForm;
use Modules\Project\Models\Project;

class ConvertToProjectAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'convertToProject';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Convert to Project')
            ->icon('heroicon-o-briefcase')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn ($record) => $record->status->value === 'won' && ! $record->projectInformations()->exists()) // Only show if Won and no PI linked? Or Project linked?
             // Since we added lead_id to Project, we should check if a project exists for this lead.
             ->visible(fn ($record) => $record->status->value === 'won' && ! Project::where('lead_id', $record->id)->exists())
            ->form([
                Select::make('project_type_id')
                    ->relationship('projectType', 'name') // Assuming relations are set on Lead or we use Model::class
                    // Helper relations might not exist on Lead directly, so best to use options or relation to Model if allowed in Action form context
                    // Actually, Select inside Action form is loosely coupled.
                    ->options(\Modules\MasterData\Models\ProjectType::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->createOptionForm(ProjectTypeForm::schema())
                    ->createOptionUsing(fn ($data) => \Modules\MasterData\Models\ProjectType::create($data)->id),

                Select::make('project_area_id')
                    ->label('Project Area')
                    ->options(\Modules\MasterData\Models\ProjectArea::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('product_cluster_id')
                    ->label('Product Cluster')
                    ->options(\Modules\MasterData\Models\ProductCluster::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('tax_id')
                    ->label('Tax')
                    ->options(\Modules\MasterData\Models\Tax::pluck('name', 'id'))
                    ->default(\Modules\MasterData\Models\Tax::where('name', 'like', '%PPN%')->first()?->id) // Best guess default
                    ->required(),

                Select::make('payment_term_id')
                    ->label('Payment Terms')
                    ->options(\Modules\MasterData\Models\PaymentTerm::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('billing_option_id')
                    ->label('Billing Option')
                    ->options(\Modules\MasterData\Models\BillingOption::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('oprep_id')
                    ->label('Oprep (Person in Charge)')
                    ->options(\Modules\MasterData\Models\Employee::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('ams_id')
                    ->label('AMS (Account Manager)')
                    ->options(\Modules\MasterData\Models\Employee::pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ])
            ->action(function (array $data, $record) {
                // $record is the Lead
                
                // Create Project
                $project = Project::create([
                    'name' => $record->title, // Or logic to generate name
                    'customer_id' => $record->customer_id,
                    'work_scheme_id' => $record->work_scheme_id,
                    'lead_id' => $record->id,
                    'status' => 'planning', // Default status
                    
                    // Merged data from form
                    'project_type_id' => $data['project_type_id'],
                    'project_area_id' => $data['project_area_id'],
                    'product_cluster_id' => $data['product_cluster_id'],
                    'tax_id' => $data['tax_id'],
                    'payment_term_id' => $data['payment_term_id'],
                    'billing_option_id' => $data['billing_option_id'],
                    'oprep_id' => $data['oprep_id'],
                    'ams_id' => $data['ams_id'],
                    
                    // Proposal link?
                    'proposal_id' => $record->proposals()->where('status', 'accepted')->first()?->id,
                ]);

                Notification::make()
                    ->title('Project Created Successfully')
                    ->body("Project linked to Lead: {$record->title}")
                    ->success()
                    ->send();
                
                // Redirect to Project Edit page
                // return redirect()->to(ProjectResource::getUrl('edit', ['record' => $project]));
                // Actions in header usually just perform action. Redirection might need proper response.
                
                $this->redirect(\Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource::getUrl('edit', ['record' => $project]));
            });
    }
}
