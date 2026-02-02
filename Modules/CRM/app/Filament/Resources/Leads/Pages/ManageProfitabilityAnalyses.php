<?php

namespace Modules\CRM\Filament\Resources\Leads\Pages;

use BackedEnum;
use Filament\Actions;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Filament\Resources\Leads\LeadResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;

class ManageProfitabilityAnalyses extends ManageRelatedRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $relationship = 'profitabilityAnalyses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $title = 'Profitability Analyses';

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record) {
            return false;
        }

        // Handle Enum casting
        $status = $record->status instanceof BackedEnum ? $record->status->value : $record->status;

        return in_array($status, [
            'proposal',
            'negotiation',
            'won',
            'closed_lost',
        ]);
    }

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
                    ->tooltip(fn (ManageProfitabilityAnalyses $livewire) => ! $livewire->getOwnerRecord()->generalInformations()->where('status', 'approved')->exists() ? 'Requires Approved Risk Register (General Information)' : null)
                    ->fillForm(function (): array {
                        $record = $this->getOwnerRecord();
                        $approvedGi = $record->generalInformations()->where('status', 'approved')->first();

                        return [
                            'customer_id' => $record->customer_id,
                            'work_scheme_id' => $record->work_scheme_id,
                            'general_information_id' => $approvedGi?->id,
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $record = $this->getOwnerRecord();
                        $data['customer_id'] = $record->customer_id;
                        $data['work_scheme_id'] = $record->work_scheme_id;
                        $data['general_information_id'] = $record->generalInformations()->where('status', 'approved')->first()?->id;

                        return $data;
                    }),
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
