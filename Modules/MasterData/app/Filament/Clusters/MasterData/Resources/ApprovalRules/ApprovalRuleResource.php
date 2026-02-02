<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules;

use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\Pages\ListApprovalRules;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\Schemas\ApprovalRuleForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\Tables\ApprovalRulesTable;
use Modules\MasterData\Models\ApprovalRule;

class ApprovalRuleResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = ApprovalRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ApprovalRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApprovalRulesTable::configure($table);
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
            'index' => ListApprovalRules::route('/'),
        ];
    }
}
