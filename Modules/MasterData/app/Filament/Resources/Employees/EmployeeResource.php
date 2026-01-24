<?php

namespace Modules\MasterData\Filament\Resources\Employees;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Resources\Employees\Pages\CreateEmployee;
use Modules\MasterData\Filament\Resources\Employees\Pages\EditEmployee;
use Modules\MasterData\Filament\Resources\Employees\Pages\ListEmployees;
use Modules\MasterData\Filament\Resources\Employees\Schemas\EmployeeForm;
use Modules\MasterData\Filament\Resources\Employees\Tables\EmployeesTable;
use Modules\MasterData\Models\Employee;

class EmployeeResource extends Resource
{
    protected static ?string $cluster = \Modules\MasterData\Filament\Clusters\MasterData\MasterDataCluster::class;

    protected static ?string $model = Employee::class;

    protected static ?int $navigationSort = 2;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
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
