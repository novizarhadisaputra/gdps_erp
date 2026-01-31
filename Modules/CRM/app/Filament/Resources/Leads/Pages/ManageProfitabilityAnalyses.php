<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Filament\Resources\Leads\LeadResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Filament\Actions;
// use Filament\Tables\Actions as TableActions;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class ManageProfitabilityAnalyses extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'profitabilityAnalyses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $title = 'Profitability Analyses';

    public function form(Schema $schema): Schema
    {
        return ProfitabilityAnalysisForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_number')
            ->columns([
                Tables\Columns\TextColumn::make('document_number'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('margin_percentage')
                    ->suffix('%')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('net_profit')
                    ->money('IDR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->disabled(fn (ManageProfitabilityAnalyses $livewire) => ! $livewire->getOwnerRecord()->generalInformations()->where('status', 'approved')->exists())
                    ->tooltip(fn (ManageProfitabilityAnalyses $livewire) => ! $livewire->getOwnerRecord()->generalInformations()->where('status', 'approved')->exists() ? 'Requires Approved Risk Register (General Information)' : null),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }
}
