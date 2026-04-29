<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\ProductCluster;

class ProductClusterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Product Cluster Identification')
                ->description('Define clusters to group products and services for organizational clarity.')
                ->schema([
                    TextInput::make('name')
                        ->label('Cluster Name')
                        ->placeholder('e.g. Software Development, Security Services')
                        ->helperText('The descriptive name of the product cluster.')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Cluster Code')
                        ->placeholder('e.g. PC-SOFT, PC-SEC')
                        ->helperText('A unique short code identifying the cluster.')
                        ->required()
                        ->unique(ProductCluster::class, 'code', ignoreRecord: true),
                ])->columns(2),

            Section::make('Visual Branding')
                ->description('Upload a logo or icon to visually represent this product cluster.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('logo')
                        ->collection('logo')
                        ->label('Cluster Logo')
                        ->image()
                        ->imageEditor()
                        ->multiple(false)
                        ->columnSpanFull()
                        ->helperText('Recommended size: 512x512px. Supported formats: PNG, JPG, SVG.'),
                ]),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this product cluster.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this cluster for new product entries.'),
                    Toggle::make('is_default')
                        ->label('Default Cluster')
                        ->default(false)
                        ->helperText('Set as the default cluster for new item registrations.'),
                ])->columns(2),
        ];
    }
}
