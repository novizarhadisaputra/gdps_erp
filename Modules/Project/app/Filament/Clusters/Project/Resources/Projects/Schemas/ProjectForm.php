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
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->placeholder('Example: Outsourcing IT Support PT Garuda')
                        ->columnSpanFull(),
                    Select::make('status')
                        ->options(ProjectStatus::class)
                        ->required()
                        ->placeholder('Select status')
                        ->native(false)
                        ->default(ProjectStatus::Planning),
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select customer')
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
                        ->afterStateUpdated(fn ($set) => $set('sourceable_id', null)),
                    Select::make('sourceable_id')
                        ->label('Source Document')
                        ->placeholder('Select document')
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
                        ->required()
                        ->placeholder('01')
                        ->helperText('Project sequence number for this customer (e.g. 01, 02)')
                        ->numeric()
                        ->default('01')
                        ->label('Sequence (e.g. 01)')
                        ->live(),
                    Select::make('project_area_id')
                        ->relationship('projectArea', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select project area')
                        ->live()
                        ->createOptionForm(ProjectAreaForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(ProjectAreaForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('work_scheme_id')
                        ->relationship('workScheme', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select work scheme')
                        ->live()
                        ->createOptionForm(WorkSchemeForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(WorkSchemeForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('product_cluster_id')
                        ->relationship('productCluster', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select product cluster')
                        ->live()
                        ->createOptionForm(ProductClusterForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(ProductClusterForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('tax_id')
                        ->relationship('tax', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->createOptionForm(TaxForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(TaxForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('payment_term_id')
                        ->relationship('paymentTerm', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm(PaymentTermForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(PaymentTermForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('project_type_id')
                        ->relationship('projectType', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm(ProjectTypeForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(ProjectTypeForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('billing_option_id')
                        ->relationship('billingOption', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm(BillingOptionForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(BillingOptionForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('oprep_id')
                        ->relationship('oprep', 'name')
                        ->label('OPREP')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm(EmployeeForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(EmployeeForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('ams_id')
                        ->relationship('ams', 'name')
                        ->label('AMS')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm(EmployeeForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->editOptionForm(EmployeeForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    DatePicker::make('start_date')
                        ->required(),
                    DatePicker::make('end_date')
                        ->required(),
                    SpatieMediaLibraryFileUpload::make('project_documents')
                        ->collection('project_documents')
                        ->label('Project Documents')

                        ->visibility('private')
                        ->multiple()
                        ->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('deliverables')
                        ->collection('deliverables')
                        ->label('Deliverables')

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
