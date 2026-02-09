<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\ProjectInformations;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Project\Filament\Clusters\Project\ProjectCluster;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectInformations\Pages\ListProjectInformations;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectInformations\Schemas\ProjectInformationForm;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectInformations\Tables\ProjectInformationTable;
use Modules\Project\Models\ProjectInformation;

class ProjectInformationResource extends Resource
{
    protected static ?string $cluster = ProjectCluster::class;

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
                Section::make('Signatures')
                    ->schema([
                        RepeatableEntry::make('signatures')
                            ->label('Digital Signatures')
                            ->schema([
                                TextEntry::make('user_name')->label('Signed By'),
                                TextEntry::make('user_role')->label('Role')->badge(),
                                TextEntry::make('type')->label('Type'),
                                TextEntry::make('signed_at')->dateTime(),
                                TextEntry::make('qr_code')
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
