<?php

namespace Modules\CRM\Filament\Resources\Leads;

use Modules\CRM\Filament\Resources\Leads\Pages;
use Modules\CRM\Models\Lead;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Filament\Resources\Leads\Schemas\LeadForm;
use Modules\CRM\Filament\Resources\Leads\Schemas\LeadInfolist;
use Modules\CRM\Filament\Resources\Leads\Tables\LeadsTable;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = CRMCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFunnel;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            \Modules\CRM\Filament\Resources\Leads\RelationManagers\ProposalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'kanban' => Pages\LeadBoard::route('/kanban'),
        ];
    }
}
