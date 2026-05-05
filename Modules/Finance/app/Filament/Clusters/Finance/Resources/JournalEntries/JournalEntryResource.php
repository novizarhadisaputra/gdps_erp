<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Clusters\Finance\FinanceCluster;
use Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Schemas\JournalEntryForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Tables\JournalEntriesTable;
use Modules\Finance\Models\JournalEntry;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $cluster = FinanceCluster::class;

    protected static ?int $navigationSort = 1;

    protected static \UnitEnum|string|null $navigationGroup = 'Accounting';

    public static function form(Schema $schema): Schema
    {
        return JournalEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'view' => Pages\ViewJournalEntry::route('/{record}'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
