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
            Section::make(__('Product Cluster Identification'))
                ->description(__('Define clusters to group products and services for organizational clarity.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Cluster Name'))
                        ->placeholder(__('e.g. Software Development, Security Services'))
                        ->helperText(__('The descriptive name of the product cluster.'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label(__('Cluster Code'))
                        ->placeholder(__('Auto-generated'))
                        ->readOnly()
                        ->unique(ProductCluster::class, 'code', ignoreRecord: true)
                        ->helperText(__('A unique identification code for this cluster.')),
                ])->columns(2),

            Section::make(__('Visual Branding'))
                ->description(__('Upload a logo or icon to visually represent this product cluster.'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('logo')
                        ->collection('logo')
                        ->label(__('Cluster Logo'))
                        ->image()
                        ->imageEditor()
                        ->multiple(false)
                        ->columnSpanFull()
                        ->helperText(__('Recommended size: 512x512px. Supported formats: PNG, JPG, SVG.')),
                ]),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this product cluster.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Enable or disable this cluster for new product entries.')),
                    Toggle::make('is_default')
                        ->label(__('Default Cluster'))
                        ->default(false)
                        ->helperText(__('Set as the default cluster for new item registrations.')),
                ])->columns(2),
        ];
    }
}
