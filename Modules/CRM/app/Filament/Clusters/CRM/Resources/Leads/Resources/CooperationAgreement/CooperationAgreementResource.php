<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\Pages\CreateCooperationAgreement;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\Pages\EditCooperationAgreement;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\Pages\ViewCooperationAgreement;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\Schemas\CooperationAgreementForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\Tables\CooperationAgreementsTable;
use Modules\CRM\Models\CooperationAgreement;

class CooperationAgreementResource extends Resource
{
    protected static ?string $model = CooperationAgreement::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    protected static ?string $parentRouteParameterName = 'lead';

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
            'create' => CreateCooperationAgreement::route('/create'),
            'view' => ViewCooperationAgreement::route('/{record}'),
            'edit' => EditCooperationAgreement::route('/{record}/edit'),
        ];
    }
}
