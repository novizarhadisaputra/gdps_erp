<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\CRMCluster;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Pages\CreateCooperationAgreement;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Pages\EditCooperationAgreement;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Pages\ListCooperationAgreements;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Pages\ViewCooperationAgreement;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Schemas\CooperationAgreementForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Tables\CooperationAgreementsTable;
use Modules\CRM\Models\CooperationAgreement;

class CooperationAgreementResource extends Resource
{
    protected static ?string $model = CooperationAgreement::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $cluster = CRMCluster::class;

    protected static ?int $navigationSort = 6;

    public static function getModelLabel(): string
    {
        return __('Cooperation Agreement');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Cooperation Agreements');
    }

    public static function getNavigationLabel(): string
    {
        return __('Cooperation Agreements');
    }

    public static function form(Schema $schema): Schema
    {
        return CooperationAgreementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CooperationAgreementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCooperationAgreements::route('/'),
            'create' => CreateCooperationAgreement::route('/create'),
            'view' => ViewCooperationAgreement::route('/{record}'),
            'edit' => EditCooperationAgreement::route('/{record}/edit'),
        ];
    }
}
