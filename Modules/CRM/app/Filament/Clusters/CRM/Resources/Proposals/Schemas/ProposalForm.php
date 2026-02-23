<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Proposal;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;

class ProposalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Proposal Details')
                ->schema([
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->hidden()
                        ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->customer_id : null)
                        ->createOptionForm(CustomerForm::schema())
                        ->createOptionAction(fn (\Filament\Actions\Action $action) => $action->slideOver())
                        ->editOptionForm(CustomerForm::schema())
                        ->editOptionAction(fn (\Filament\Actions\Action $action) => $action->slideOver()),
                    Select::make('lead_id')
                        ->relationship('lead', 'title')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->hidden()
                        ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set) {
                            if (! $state) {
                                return;
                            }
                            $lead = \Modules\CRM\Models\Lead::find($state);
                            if (! $lead) {
                                return;
                            }
                            $set('customer_id', $lead->customer_id);
                            $set('work_scheme_id', $lead->work_scheme_id);
                            $set('amount', $lead->estimated_amount);
                        }),
                    Select::make('work_scheme_id')
                        ->relationship('workScheme', 'name')
                        ->searchable()
                        ->preload()
                        ->disabled() // Inherited from Lead rarely changes at this stage
                        ->dehydrated()
                        ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->work_scheme_id : null)
                        ->createOptionForm(WorkSchemeForm::schema())
                        ->createOptionAction(fn (\Filament\Actions\Action $action) => $action->slideOver())
                        ->editOptionForm(WorkSchemeForm::schema())
                        ->editOptionAction(fn (\Filament\Actions\Action $action) => $action->slideOver()),
                    TextInput::make('proposal_number')
                        ->required()
                        ->hiddenOn('create') // Auto-generated
                        ->unique(Proposal::class, 'proposal_number', ignoreRecord: true),
                    TextInput::make('amount')
                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                        ->prefix('IDR')
                        ->default(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords ? $livewire->getOwnerRecord()->estimated_amount : 0)
                        ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                        ->required(),
                    ToggleButtons::make('status')
                        ->options(ProposalStatus::class)
                        ->default(ProposalStatus::Draft)
                        ->hiddenOn('create')
                        ->disabled()
                        ->inline()
                        ->required(),
                    DatePicker::make('submission_date'),
                    SpatieMediaLibraryFileUpload::make('final_proposal')
                        ->collection('final_proposal')
                        ->label('Final Proposal Document')
                        ->disk('s3')
                        ->visibility('private')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    protected static function parseCurrency($value): float
    {
        if (! $value) {
            return 0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = str_replace('.', '', $value);
        $clean = str_replace(',', '.', $clean);

        return (float) $clean;
    }
}
