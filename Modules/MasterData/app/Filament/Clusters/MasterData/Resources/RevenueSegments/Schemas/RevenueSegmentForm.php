<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\RevenueSegment;

class RevenueSegmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(RevenueSegment::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Revenue Segment Definition')
                ->description('Define revenue segments for financial reporting and business unit alignment.')
                ->schema([
                    TextInput::make('name')
                        ->label('Segment Name')
                        ->placeholder('e.g. Aviation, Logistics, Ground Handling')
                        ->helperText('The descriptive name of the revenue segment.')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Segment Code')
                        ->placeholder('e.g. RS-AVIA, RS-LOG')
                        ->helperText('A unique short code identifying the revenue segment.')
                        ->required()
                        ->unique(RevenueSegment::class, 'code', ignoreRecord: true),
                    Select::make('unit_id')
                        ->relationship('unit', 'name')
                        ->label('Associated Unit')
                        ->placeholder('Select unit')
                        ->helperText('The business unit this revenue segment belongs to.')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->visible(fn () => auth()->user()->can('view_all_master_data')),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this revenue segment.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this segment for financial entries.'),
                    Toggle::make('is_default')
                        ->label('Default Segment')
                        ->default(false)
                        ->helperText('Set as the default segment for new revenue configurations.'),
                ])->columns(2),
        ];
    }
}
