<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Models\ManpowerTemplate;

class ManpowerTemplateResource extends Resource
{
    protected static ?string $model = ManpowerTemplate::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    protected static ?string $navigationLabel = 'Manpower Costing';

    protected static ?string $pluralLabel = 'Manpower Costing';

    protected static ?string $singularLabel = 'Manpower Costing';

    protected static ?string $slug = 'manpower-costing';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return Schemas\ManpowerTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\ManpowerTemplatesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return Schemas\ManpowerTemplateInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManpowerTemplates::route('/'),
            'create' => Pages\CreateManpowerTemplate::route('/create'),
            'view' => Pages\ViewManpowerTemplate::route('/{record}'),
            'edit' => Pages\EditManpowerTemplate::route('/{record}/edit'),
        ];
    }
}
