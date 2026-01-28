<?php

namespace Modules\MasterData\Filament\Resources\Employees;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster;
use Modules\MasterData\Filament\Resources\Employees\Pages\ListEmployees;
use Modules\MasterData\Filament\Resources\Employees\Schemas\EmployeeForm;
use Modules\MasterData\Filament\Resources\Employees\Schemas\EmployeeInfolist;
use Modules\MasterData\Filament\Resources\Employees\Tables\EmployeesTable;
use Modules\MasterData\Models\Employee;

class EmployeeResource extends Resource
{
    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $model = Employee::class;

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Human Resources';

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
        ];
    }
}
