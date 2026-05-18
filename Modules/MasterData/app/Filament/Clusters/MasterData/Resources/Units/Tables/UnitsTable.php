<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Tables;

use App\Services\SsoAuthService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
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
                    ->label(__('Sync from API'))
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->modalHeading(__('Sync Units from SSO'))
                    ->modalDescription(__('This will fetch and update unit records from the SSO API.'))
                    ->schema([
                        TextInput::make('password')
                            ->label(__('SSO Password'))
                            ->password()
                            ->required()
                            ->visible(fn () => auth()->user()->isTokenExpired())
                            ->helperText(__('Your SSO session has expired. Please enter your password to re-authenticate and proceed with the sync.')),
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
                                    ->title(__('Re-authentication Successful'))
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title(__('Re-authentication Failed'))
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
                                ? __('Your SSO session is invalid. If you just re-authenticated, please try again.')
                                : __('No data was returned from the API.');

                            Notification::make()
                                ->title(__('Sync Failed'))
                                ->body($reason)
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(__('Sync Completed'))
                            ->body(__('Successfully synced :count units.', ['count' => $synced->count()]))
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                TextColumn::make('external_id')
                    ->label(__('SSO ID'))
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
                    ->label(__('Superior Unit'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('Default'))
                    ->boolean()
                    ->trueIcon(Heroicon::Star)
                    ->falseIcon(null)
                    ->color('warning')
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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('permissions')
                        ->label(__('Manage Permissions'))
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->color('primary')
                        ->url(fn (Unit $record): string => UnitResource::getUrl('permissions', ['record' => $record])),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
            ])
            ->defaultPaginationPageOption(50);
    }
}
