<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas\CustomerForm;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BillingOptions\Schemas\BillingOptionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Schemas\EmployeeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas\PaymentTermForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Schemas\ProjectTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Taxes\Schemas\TaxForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Enums\ProjectStatus;

class ProjectForm
{
    public static function schema(): array
    {
        return [
            Section::make('Project Details')
                ->description('Core information including project name, customer alignment, document references, and numbering segments.')
                ->schema([
                    TextEntry::make('number')
                        ->label('Project Code Preview')
                        ->state(function (Get $get): string {
                            $customer = Customer::find($get('customer_id'))?->code ?? 'UNK';
                            $seq = str_pad($get('project_number') ?? '01', 2, '0', STR_PAD_LEFT);
                            $area = ProjectArea::find($get('project_area_id'))?->code ?? 'UNK';
                            $scheme = WorkScheme::find($get('work_scheme_id'))?->code ?? '00';
                            $cluster = ProductCluster::find($get('product_cluster_id'))?->code ?? 'UNK';
                            $tax = Tax::find($get('tax_id'))?->code ?? 'P0';

                            return "{$customer}{$seq}{$area}{$scheme}{$cluster}{$tax}";
                        })
                        ->columnSpanFull(),
                    TextInput::make('name')
                        ->label('Project Name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->placeholder('e.g. Outsourcing IT Support PT Garuda')
                        ->helperText('A descriptive name for the project.')
                        ->columnSpanFull(),
                    Select::make('status')
                        ->label('Project Status')
                        ->options(ProjectStatus::class)
                        ->required()
                        ->placeholder('Select status')
                        ->native(false)
                        ->default(ProjectStatus::Planning)
                        ->helperText('The current lifecycle stage of this project.'),
                    Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select customer')
                        ->helperText('The primary client for this project.')
                        ->live()
                        ->createOptionForm(CustomerForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(CustomerForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('sourceable_type')
                        ->label('Legal Basis Type')
                        ->options([
                            \Modules\CRM\Models\PurchaseOrder::class => 'Purchase Order (PO)',
                            \Modules\CRM\Models\WorkOrder::class => 'Work Order (SPK)',
                            \Modules\CRM\Models\CooperationAgreement::class => 'Cooperation Agreement (PKS)',
                        ])
                        ->live()
                        ->placeholder('Select document type')
                        ->helperText('The type of document that serves as the legal foundation for this project.')
                        ->afterStateUpdated(fn ($set) => $set('sourceable_id', null)),
                    Select::make('sourceable_id')
                        ->label('Source Document')
                        ->placeholder('Select reference document')
                        ->helperText('The specific legal document (PO/SPK/PKS) for this project.')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->options(function (Get $get) {
                            $type = $get('sourceable_type');
                            $customerId = $get('customer_id');
                            if (! $type) {
                                return [];
                            }

                            return $type::query()
                                ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
                                ->get()
                                ->pluck('number', 'id');
                        })
                        ->visible(fn (Get $get) => filled($get('sourceable_type'))),
                    TextInput::make('project_number')
                        ->label('Project Sequence')
                        ->required()
                        ->placeholder('01')
                        ->helperText('Sequence number for this specific customer (e.g., 01, 02).')
                        ->numeric()
                        ->default('01')
                        ->live(),
                    Select::make('project_area_id')
                        ->label('Project Area')
                        ->relationship(
                            name: 'projectArea',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query, Get $get) => $query->whereHas('customers', fn ($q) => $q->where('customers.id', $get('customer_id')))
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select project area')
                        ->helperText('The geographic or organizational area of the project.')
                        ->live()
                        ->visible(fn (Get $get) => filled($get('customer_id')))
                        ->createOptionForm(ProjectAreaForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->createOptionUsing(function (array $data, Get $get) {
                            $area = ProjectArea::create($data);
                            $customerId = $get('customer_id');
                            if ($customerId) {
                                $area->customers()->attach($customerId);
                            }

                            return $area->id;
                        })
                        ->editOptionForm(ProjectAreaForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('work_scheme_id')
                        ->label('Work Scheme')
                        ->relationship('workScheme', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select work scheme')
                        ->helperText('The operational model or scheme used for this project.')
                        ->live()
                        ->createOptionForm(WorkSchemeForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(WorkSchemeForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('product_cluster_id')
                        ->label('Product Cluster')
                        ->relationship('productCluster', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select product cluster')
                        ->helperText('The grouping of products/services for reporting.')
                        ->live()
                        ->createOptionForm(ProductClusterForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(ProductClusterForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('tax_id')
                        ->label('Tax Category')
                        ->relationship('tax', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select tax code')
                        ->helperText('The applicable tax classification for this project.')
                        ->live()
                        ->createOptionForm(TaxForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(TaxForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('payment_term_id')
                        ->label('Payment Terms')
                        ->relationship('paymentTerm', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select payment term')
                        ->helperText('The agreed schedule for client payments.')
                        ->createOptionForm(PaymentTermForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(PaymentTermForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('project_type_id')
                        ->label('Project Type')
                        ->relationship('projectType', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select type')
                        ->helperText('Classification of the project nature.')
                        ->createOptionForm(ProjectTypeForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(ProjectTypeForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('billing_option_id')
                        ->label('Billing Option')
                        ->relationship('billingOption', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select billing method')
                        ->helperText('The mechanism for invoicing (e.g., Monthly, Milestone).')
                        ->createOptionForm(BillingOptionForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(BillingOptionForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('oprep_id')
                        ->relationship('oprep', 'name')
                        ->label('OPREP (Operation Representative)')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select OPREP')
                        ->helperText('The internal person responsible for project operations.')
                        ->createOptionForm(EmployeeForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(EmployeeForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('ams_id')
                        ->relationship('ams', 'name')
                        ->label('AMS (Account Management)')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select AMS')
                        ->helperText('The person responsible for account relationship management.')
                        ->createOptionForm(EmployeeForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(EmployeeForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('revenue_segment_id')
                        ->label('Revenue Segment')
                        ->relationship('revenueSegment', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select revenue segment')
                        ->helperText('Determines the customer group (e.g., GA Group, Third Parties).'),
                    DatePicker::make('start_date')
                        ->label('Start Date')
                        ->placeholder('Select start date')
                        ->helperText('The official commencement date of the project.')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('End Date')
                        ->placeholder('Select end date')
                        ->helperText('The planned completion date of the project.')
                        ->required(),
                    SpatieMediaLibraryFileUpload::make('project_documents')
                        ->collection('project_documents')
                        ->label('General Project Documents')
                        ->helperText('Upload any supporting documents, contracts, or reference materials.')
                        ->visibility('private')
                        ->multiple()
                        ->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('deliverables')
                        ->collection('deliverables')
                        ->label('Key Deliverables')
                        ->helperText('Upload major project output documents or final reports.')
                        ->visibility('private')
                        ->multiple()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }
}
