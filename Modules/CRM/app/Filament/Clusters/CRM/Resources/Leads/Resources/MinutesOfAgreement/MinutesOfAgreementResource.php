<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Schemas\MinutesOfAgreementForm;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Tables\MinutesOfAgreementsTable;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages\ListMinutesOfAgreements;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages\CreateMinutesOfAgreement;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages\EditMinutesOfAgreement;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages\ViewMinutesOfAgreement;
use Modules\CRM\Models\MinutesOfAgreement;

class MinutesOfAgreementResource extends Resource
{
    protected static ?string $model = MinutesOfAgreement::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    public static function form(Schema $schema): Schema
    {
        return MinutesOfAgreementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MinutesOfAgreementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMinutesOfAgreements::route('/'),
            'create' => CreateMinutesOfAgreement::route('/create'),
            'edit' => EditMinutesOfAgreement::route('/{record}/edit'),
            'view' => ViewMinutesOfAgreement::route('/{record}'),
        ];
    }
}
