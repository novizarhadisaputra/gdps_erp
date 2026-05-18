<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\ContactRole;

class ContactRoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make(__('Role Identification'))
                ->description(__('Define the roles that contacts can hold within an organization or project.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Role Name'))
                        ->placeholder(__('e.g. Project Manager, Decision Maker, Technical Contact'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ContactRole::class, 'name', ignoreRecord: true)
                        ->helperText(__('The descriptive name of the contact role.')),
                    TextInput::make('code')
                        ->label(__('Role Code'))
                        ->placeholder(__('e.g. ROLE-PM, ROLE-DM'))
                        ->required()
                        ->unique(ContactRole::class, 'code', ignoreRecord: true)
                        ->helperText(__('Unique short code identifying this role.')),
                    Textarea::make('description')
                        ->label(__('Role Description'))
                        ->placeholder(__('Describe the responsibilities or context of this role...'))
                        ->maxLength(65535)
                        ->columnSpanFull()
                        ->helperText(__('Additional details about this contact role.')),
                ])->columns(2),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this contact role.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Inactive roles will not be available for new contacts.')),
                    Toggle::make('is_default')
                        ->label(__('Default Role'))
                        ->default(false)
                        ->helperText(__('Set as the default role for primary contact persons.')),
                ])->columns(2),
        ];
    }
}
