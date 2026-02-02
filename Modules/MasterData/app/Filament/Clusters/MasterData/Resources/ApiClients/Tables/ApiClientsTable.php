<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApiClients\Tables;

use App\Models\ApiClient;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ApiClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client_id')
                    ->label('Client ID')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('last_used_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never used'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                Action::make('regenerate_secret')
                    ->label('Regenerate Secret')
                    ->icon(Heroicon::ArrowPath)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate Client Secret')
                    ->modalDescription('Are you sure you want to regenerate the secret? The old secret will stop working immediately.')
                    ->modalSubmitActionLabel('Regenerate')
                    ->action(function (ApiClient $record) {
                        $newSecret = Str::random(32);
                        $record->update([
                            'client_secret' => $newSecret,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Secret Regenerated')
                            ->body("New Secret: **$newSecret**\n\nPlease copy this now.")
                            ->warning()
                            ->persistent()
                            ->send();
                    }),
            ]);
    }
}
