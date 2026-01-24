<?php

namespace Modules\CRM\Filament\Resources\Proposals\Schemas;

use Filament\Schemas\Schema;

class ProposalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                \Filament\Forms\Components\TextInput::make('proposal_number')
                    ->required()
                    ->unique(ignoreRecord: true),
                \Filament\Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('IDR')
                    ->required(),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'converted' => 'Converted',
                    ])
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('submission_date'),
            ]);
    }
}
