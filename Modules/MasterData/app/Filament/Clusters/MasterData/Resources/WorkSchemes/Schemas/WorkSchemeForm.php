<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\WorkScheme;

class WorkSchemeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(WorkScheme::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make(__('Work Scheme Configuration'))
                ->description(__('Define the scheduling and attendance schemes for different employee groups.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Scheme Name'))
                        ->placeholder(__('e.g. 5/2 Office, 2-2-2 Shift, Remote'))
                        ->helperText(__('The descriptive name of the work scheme.'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label(__('Scheme Code'))
                        ->placeholder(__('Auto-generated'))
                        ->readOnly()
                        ->unique(WorkScheme::class, 'code', ignoreRecord: true)
                        ->helperText(__('A unique identification code for this work scheme.')),
                ])->columns(2),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this work scheme.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Enable or disable this scheme for assignment.')),
                    Toggle::make('is_default')
                        ->label(__('Default Scheme'))
                        ->default(false)
                        ->helperText(__('Set as the default scheme for new employee profiles.')),
                ])->columns(2),
        ];
    }
}
