<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;

class MeetingRelationManager extends RelationManager
{
    protected static string $relationship = 'meetings';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            
            DateTimePicker::make('date')
                ->required()
                ->default(now()),
            
            TextInput::make('location')
                ->label('Location / Link')
                ->maxLength(255),
            
            Repeater::make('notes')
                ->label('Meeting Minutes (MoM)')
                ->schema([
                    TextInput::make('point')
                        ->label('Discussion Point')
                        ->required()
                        ->columnSpan(2),
                    Select::make('type')
                        ->options([
                            'discussion' => 'Discussion',
                            'decision' => 'Decision',
                            'action_item' => 'Action Item',
                        ])
                        ->default('discussion')
                        ->required()
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->columnSpanFull()
                ->collapsible(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('location')
                    ->limit(30),
                TextColumn::make('user.name')
                    ->label('Created By'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
