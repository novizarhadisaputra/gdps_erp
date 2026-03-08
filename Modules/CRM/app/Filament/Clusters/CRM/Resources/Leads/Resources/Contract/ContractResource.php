<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Pages\CreateContract;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Pages\EditContract;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Pages\ListContracts;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Pages\ViewContract;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Schemas\ContractForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Schemas\ContractInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Tables\ContractsTable;
use Modules\CRM\Models\Contract;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    public static function form(Schema $schema): Schema
    {
        return ContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContractInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContracts::route('/'),
            'create' => CreateContract::route('/create'),
            'view' => ViewContract::route('/{record}'),
            'edit' => EditContract::route('/{record}/edit'),
        ];
    }
}
