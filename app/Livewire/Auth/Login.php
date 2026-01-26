<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\SsoAuthService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Login as LoginEvent;
use Illuminate\Validation\ValidationException;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Services\UnitService;
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

            // Step 1: Attempt SSO Authentication
            try {
                $authData = $ssoService->login($this->email, $this->password);

                // Step 2: Get employee data from SSO
                $employeeData = $ssoService->getEmployeeData(
                    $this->email,
                    $authData['accessToken']
                );

                // Step 3: Create or update user from SSO data
                $user = User::updateOrCreate(
                    ['email' => $this->email],
                    [
                        'name' => $employeeData['NAMA'] ?? $this->email,
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

                // Step 4: Sync to local Employees master data
                Employee::updateOrCreate(
                    ['code' => $user->employee_code],
                    [
                        'unit_id' => $user->unit_id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'position' => $user->position,
                        'department' => $user->unit,
                        'status' => 'active',
                    ]
                );

                // Step 5: Refresh and Warm-up Units Cache
                UnitService::clearCache();
                app(UnitService::class)->getAllUnits();

                // Assign default role for new SSO users if they don't have one
                if ($user->roles()->count() === 0) {
                    $user->assignRole('panel_user');
                }

                Filament::auth()->login($user, $this->remember);
            } catch (\Exception $ssoException) {
                // Step 4: Fallback to Local Authentication if SSO fails
                if (! Filament::auth()->attempt([
                    'email' => $this->email,
                    'password' => $this->password,
                ], $this->remember)) {
                    throw new \Exception('Invalid credentials (SSO & Local fallback failed).');
                }

                $user = User::query()->where('email', $this->email)->first();
            }

            session()->regenerate();

            // Fire Login event for plugins like Shield or Logger
            event(new LoginEvent('web', $user, $this->remember));

            return redirect()->to(Filament::getUrl());
        } catch (\Exception $e) {
            \Log::error('Login process failed', [
                'message' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Authentication Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }

    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
