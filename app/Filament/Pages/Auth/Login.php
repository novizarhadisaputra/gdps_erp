<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\SsoAuthService;
use Exception;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->extraInputAttributes(['tabindex' => 2]),
            ]);
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $data = $this->form->getState();

            // Initialize SSO service
            $ssoService = app(SsoAuthService::class);

            // Step 1: Authenticate with SSO
            $authData = $ssoService->login($data['email'], $data['password']);

            // Step 2: Get employee data
            $employeeData = $ssoService->getEmployeeData(
                $data['email'],
                $authData['accessToken']
            );

            // Step 3: Create or update user
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $employeeData['NAMA'],
                    'employee_code' => $employeeData['NOPEG'],
                    'company' => $employeeData['PERUSAHAAN'],
                    'unit_id' => $employeeData['ORGANISASI_ID'],
                    'unit' => $employeeData['ORGANISASI'],
                    'position_id' => $employeeData['POSISI_ID'],
                    'position' => $employeeData['POSISI'],
                    'mobile_phone' => $employeeData['MOBILE_PHONE'],
                    'access_token' => $authData['accessToken'],
                    'refresh_token' => $authData['refreshToken'],
                    'token_expires_at' => now()->addSeconds($authData['expiresIn']),
                ]
            );

            // Step 4: Authenticate user in Laravel
            Auth::login($user, remember: true);

            session()->regenerate();

            return app(LoginResponse::class);
        } catch (Exception $e) {
            Notification::make()
                ->title('Authentication Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }
    }
}
