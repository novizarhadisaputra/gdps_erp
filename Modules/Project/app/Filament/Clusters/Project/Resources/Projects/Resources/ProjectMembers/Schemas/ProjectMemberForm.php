<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectMembers\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\Vendor;

class ProjectMemberForm
{
    public static function schema(): array
    {
        return [
            Select::make('memberable_type')
                ->label('Member Type')
                ->options([
                    Employee::class => 'Internal Employee',
                    Vendor::class => 'External Vendor (Company)',
                    User::class => 'External Individual (User)',
                ])
                ->required()
                ->live()
                ->placeholder('Select type'),
            Select::make('memberable_id')
                ->label('Member')
                ->options(fn (Get $get) => match ($get('memberable_type')) {
                    Employee::class => Employee::query()->pluck('name', 'id'),
                    Vendor::class => Vendor::query()->pluck('name', 'id'),
                    User::class => User::query()->pluck('name', 'id'),
                    default => [],
                })
                ->required()
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => filled($get('memberable_type')))
                ->placeholder('Select member'),
            Select::make('role')
                ->options([
                    'Project Manager' => 'Project Manager',
                    'Asst. Project Manager' => 'Asst. Project Manager',
                    'Site Manager' => 'Site Manager',
                    'Team Lead' => 'Team Lead',
                    'Staff' => 'Staff',
                    'Consultant' => 'Consultant',
                    'Subcontractor' => 'Subcontractor',
                ])
                ->required()
                ->searchable()
                ->placeholder('Select role'),
            DatePicker::make('joined_at')
                ->default(now())
                ->required(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }
}
