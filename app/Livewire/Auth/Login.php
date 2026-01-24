<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\SsoAuthService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Login as LoginEvent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Login - GDPS ERP')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function login(): mixed
    {
        $this->validate();

        try {
            $ssoService = app(SsoAuthService::class);

            // Step 1: Authenticate with SSO
            $authData = $ssoService->login($this->email, $this->password);

            // Step 2: Get employee data
            $employeeData = $ssoService->getEmployeeData(
                $this->email,
                $authData['accessToken']
            );

            // Step 3: Create or update user
            $user = User::where('email', '=', $this->email)->first();

            $user = User::updateOrCreate(
                ['email' => $this->email],
                [
                    'name' => $employeeData['NAMA'] ?? $this->email,
                    'password' => $user?->password ?? bcrypt(str()->random(32)),
                    'employee_code' => $employeeData['NOPEG'] ?? null,
                    'company' => $employeeData['PERUSAHAAN'] ?? null,
                    'unit_id' => $employeeData['ORGANISASI_ID'] ?? null,
                    'unit' => $employeeData['ORGANISASI'] ?? null,
                    'position_id' => $employeeData['POSISI_ID'] ?? null,
                    'position' => $employeeData['POSISI'] ?? null,
                    'mobile_phone' => $employeeData['MOBILE_PHONE'] ?? null,
                    'access_token' => $authData['accessToken'],
                    'refresh_token' => $authData['refreshToken'],
                    'token_expires_at' => now()->addSeconds($authData['expiresIn']),
                ]
            );

            // Step 4: Authenticate user in Laravel via Filament's guard
            \Log::info('Proceeding to login user', ['email' => $this->email]);

            session()->regenerate();

            Filament::auth()->login($user, $this->remember);

            // Fire Login event for plugins like Shield or Logger
            event(new LoginEvent('web', $user, $this->remember));

            \Log::info('User login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'is_authed' => Filament::auth()->check(),
                'filament_url' => Filament::getUrl(),
            ]);

            // Handle redirect via RedirectResponse
            return redirect()->to(Filament::getUrl());
        } catch (\Exception $e) {
            \Log::error('Login process failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Authentication Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->addError('email', 'Authentication failed');
        }

        return null;
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
