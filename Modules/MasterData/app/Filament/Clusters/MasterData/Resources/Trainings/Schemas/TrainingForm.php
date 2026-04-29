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
            Section::make('Training Definition')
                ->description('Define technical or safety training required for personnel.')
                ->schema([
                    TextInput::make('name')
                        ->label('Training Name')
                        ->placeholder('e.g. K3 Sertifikasi, Security Guard Training')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Training Code')
                        ->placeholder('e.g. TRN-K3, TRN-SEC')
                        ->unique(Training::class, 'code', ignoreRecord: true)
                        ->helperText('Unique identifier for this training type.'),
                ])->columns(2),

            Section::make('Costing & Validity')
                ->description('Set the standard cost and expiration duration for this training.')
                ->schema([
                    TextInput::make('base_cost')
                        ->label('Standard Cost')
                        ->numeric()
                        ->prefix('IDR')
                        ->placeholder('0.00')
                        ->default(0)
                        ->helperText('Default training fee per person.'),
                    TextInput::make('validity_period')
                        ->label('Validity Period (Months)')
                        ->numeric()
                        ->placeholder('e.g. 24')
                        ->helperText('Duration before re-certification is required (leave empty if non-expiring).'),
                ])->columns(2),

            Section::make('Additional Information')
                ->description('Provide extra details and manage the availability of this training.')
                ->schema([
                    Textarea::make('description')
                        ->label('Detailed Description')
                        ->placeholder('Explain the training objectives and scope...')
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Inactive training cannot be assigned to new templates.'),
                    Toggle::make('is_default')
                        ->label('Default Training')
                        ->default(false)
                        ->helperText('Sets this as a default training for standard manpower costing.'),
                ])->columns(2),
        ];
    }
}
