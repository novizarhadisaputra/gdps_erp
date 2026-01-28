<?php

namespace Modules\Project\Filament\Resources\ProjectInformations;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Project\Filament\Resources\ProjectInformations\Pages\ListProjectInformations;
use Modules\Project\Filament\Resources\ProjectInformations\Schemas\ProjectInformationForm;
use Modules\Project\Filament\Resources\ProjectInformations\Tables\ProjectInformationTable;
use Modules\Project\Models\ProjectInformation;

class ProjectInformationResource extends Resource
{
    protected static ?string $cluster = \Modules\Project\Filament\Clusters\Project\ProjectCluster::class;

    protected static ?string $model = ProjectInformation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Project Info';

    protected static ?string $modelLabel = 'Project Information';

    public static function form(Schema $schema): Schema
    {
        return ProjectInformationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectInformationTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Signatures')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('signatures')
                            ->label('Digital Signatures')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('user_name')->label('Signed By'),
                                \Filament\Infolists\Components\TextEntry::make('user_role')->label('Role')->badge(),
                                \Filament\Infolists\Components\TextEntry::make('type')->label('Type'),
                                \Filament\Infolists\Components\TextEntry::make('signed_at')->dateTime(),
                                \Filament\Infolists\Components\TextEntry::make('qr_code')
                                    ->label('QR Verification')
                                    ->html()
                                    ->extraAttributes(['class' => 'w-32 h-32']),
                            ])
                            ->columns(5)
                            ->grid(2),
                    ]),
            ]);
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
            'index' => ListProjectInformations::route('/'),
        ];
    }
}
