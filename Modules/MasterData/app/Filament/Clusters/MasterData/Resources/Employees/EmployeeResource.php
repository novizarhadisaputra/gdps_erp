<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Pages\CreateEmployee;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Pages\EditEmployee;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Pages\ListEmployees;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Pages\ViewEmployee;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Schemas\EmployeeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Schemas\EmployeeInfolist;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Tables\EmployeesTable;
use Modules\MasterData\Models\Employee;

class EmployeeResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = Employee::class;

    protected static ?int $navigationSort = 3;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'HR & Organization';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema);
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
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Employee');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Employees');
    }

    public static function getNavigationLabel(): string
    {
        return __('Employees');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR & Organization');
    }
}
