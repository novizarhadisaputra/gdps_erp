<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Trainings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\Training;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make(__('Training Definition'))
                ->description(__('Define technical or safety training required for personnel.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Training Name'))
                        ->placeholder(__('e.g. K3 Sertifikasi, Security Guard Training'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label(__('Training Code'))
                        ->placeholder(__('e.g. TRN-K3, TRN-SEC'))
                        ->unique(Training::class, 'code', ignoreRecord: true)
                        ->helperText(__('Unique identifier for this training type.')),
                ])->columns(2),

            Section::make(__('Costing & Validity'))
                ->description(__('Set the standard cost and expiration duration for this training.'))
                ->schema([
                    TextInput::make('base_cost')
                        ->label(__('Standard Cost'))
                        ->numeric()
                        ->prefix('IDR')
                        ->placeholder(__('0.00'))
                        ->default(0)
                        ->helperText(__('Default training fee per person.')),
                    TextInput::make('validity_period')
                        ->label(__('Validity Period (Months)'))
                        ->numeric()
                        ->placeholder(__('e.g. 24'))
                        ->helperText(__('Duration before re-certification is required (leave empty if non-expiring).')),
                ])->columns(2),

            Section::make(__('Additional Information'))
                ->description(__('Provide extra details and manage the availability of this training.'))
                ->schema([
                    Textarea::make('description')
                        ->label(__('Detailed Description'))
                        ->placeholder(__('Explain the training objectives and scope...'))
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Inactive training cannot be assigned to new templates.')),
                    Toggle::make('is_default')
                        ->label(__('Default Training'))
                        ->default(false)
                        ->helperText(__('Sets this as a default training for standard manpower costing.')),
                ])->columns(2),
        ];
    }
}
