<?php

namespace Modules\Project\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('code')
                    ->label('Project Code Preview')
                    ->content(function (\Filament\Schemas\Components\Utilities\Get $get): string {
                        $client = \Modules\MasterData\Models\Client::find($get('client_id'))?->code ?? 'UNK';
                        $seq = str_pad($get('project_number') ?? '01', 2, '0', STR_PAD_LEFT);
                        $area = \Modules\MasterData\Models\ProjectArea::find($get('project_area_id'))?->code ?? 'UNK';
                        $scheme = \Modules\MasterData\Models\WorkScheme::find($get('work_scheme_id'))?->code ?? '00';
                        $cluster = \Modules\MasterData\Models\ProductCluster::find($get('product_cluster_id'))?->code ?? 'UNK';
                        $tax = \Modules\MasterData\Models\Tax::find($get('tax_id'))?->code ?? 'P0';

                        return "{$client}{$seq}{$area}{$scheme}{$cluster}{$tax}";
                    })
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true),
                Select::make('status')
                    ->options([
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('planning'),
                Select::make('client_id')
                    ->relationship('client', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('contract_id')
                    ->relationship('contract', 'contract_number', fn ($query, $get) => $query->where('client_id', $get('client_id')))
                    ->label('Contract/SPK')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->visible(fn ($get) => filled($get('client_id'))),
                TextInput::make('project_number')
                    ->required()
                    ->numeric()
                    ->default('01')
                    ->label('Sequence (e.g. 01)')
                    ->live(),
                Select::make('project_area_id')
                    ->relationship('projectArea', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('work_scheme_id')
                    ->relationship('workScheme', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('product_cluster_id')
                    ->relationship('productCluster', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('tax_id')
                    ->relationship('tax', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('payment_term_id')
                    ->relationship('paymentTerm', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('project_type_id')
                    ->relationship('projectType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('billing_option_id')
                    ->relationship('billingOption', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('oprep_id')
                    ->relationship('oprep', 'name')
                    ->label('OPREP')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('ams_id')
                    ->relationship('ams', 'name')
                    ->label('AMS')
                    ->required()
                    ->searchable()
                    ->preload(),
                \Filament\Forms\Components\DatePicker::make('start_date')
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('end_date')
                    ->required(),
            ]);
    }
}
