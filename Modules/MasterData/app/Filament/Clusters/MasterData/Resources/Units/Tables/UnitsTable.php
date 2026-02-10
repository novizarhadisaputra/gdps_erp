<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Tables;

use App\Services\SsoAuthService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\UnitResource;
use Modules\MasterData\Models\Unit;
use Modules\MasterData\Services\UnitService;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('sync')
                    ->label('Sync from API')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->modalHeading('Sync Units from SSO')
                    ->modalDescription('This will fetch and update unit records from the SSO API.')
                    ->schema([
                        TextInput::make('password')
                            ->label('SSO Password')
                            ->password()
                            ->required()
                            ->visible(fn () => auth()->user()->isTokenExpired())
                            ->helperText('Your SSO session has expired. Please enter your password to re-authenticate and proceed with the sync.'),
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();

                        // 1. If password provided, re-authenticate first
                        if (! empty($data['password'])) {
                            try {
                                $ssoService = app(SsoAuthService::class);
                                $authData = $ssoService->login($user->email, $data['password']);

                                $user->update([
                                    'access_token' => $authData['accessToken'],
                                    'refresh_token' => $authData['refreshToken'],
                                    'token_expires_at' => now()->addSeconds($authData['expiresIn']),
                                ]);

                                Notification::make()
                                    ->title('Re-authentication Successful')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Re-authentication Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();

                                return;
                            }
                        }

                        // 2. Proceed with sync
                        /** @var UnitService $service */
                        $service = app(UnitService::class);
                        $synced = $service->syncFromApi();

                        if ($synced->isEmpty()) {
                            $reason = (! $user || ! $user->access_token)
                                ? 'Your SSO session is invalid. If you just re-authenticated, please try again.'
                                : 'No data was returned from the API.';

                            Notification::make()
                                ->title('Sync Failed')
                                ->body($reason)
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Sync Completed')
                            ->body("Successfully synced {$synced->count()} units.")
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                TextColumn::make('external_id')
                    ->label('SSO ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('superior_unit')
                    ->label('Superior Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('permissions')
                    ->label('Manage Permissions')
                    ->icon('heroicon-o-shield-check')
                    ->color('primary')
                    ->url(fn (Unit $record): string => UnitResource::getUrl('permissions', ['record' => $record])),
            ]);
    }
}
