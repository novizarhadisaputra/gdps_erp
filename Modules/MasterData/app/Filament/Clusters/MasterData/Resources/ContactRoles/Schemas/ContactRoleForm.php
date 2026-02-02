<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContactRoles\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->unique(ContactRole::class, 'name', ignoreRecord: true),
            Textarea::make('description')
                ->maxLength(65535)
                ->columnSpanFull(),
        ];
    }
}
