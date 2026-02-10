<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\MasterData\Models\Unit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, InteractsWithMedia, Notifiable;

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // For now, allow all authenticated users.
        // If using Shield, this might be handled by permissions.
        return true;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_code',
        'company',
        'unit_id',
        'unit',
        'position_id',
        'position',
        'mobile_phone',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'signature_pin',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('signature')
            ->singleFile();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'signature_pin' => 'hashed',
        ];
    }

    /**
     * Check if the access token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (! $this->token_expires_at) {
            return true;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Check if the token needs refresh (expires in less than 5 minutes).
     */
    public function needsTokenRefresh(): bool
    {
        if (! $this->token_expires_at) {
            return true;
        }

        return $this->token_expires_at->isBefore(now()->addMinutes(5));
    }

    /**
     * Refresh the SSO access token.
     *
     * @return bool True if successful, false otherwise.
     */
    public function refreshSsoToken(): bool
    {
        if (! $this->refresh_token) {
            return false;
        }

        try {
            $ssoService = app(\App\Services\SsoAuthService::class);
            $tokenData = $ssoService->refreshAccessToken($this->refresh_token);

            $this->update([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? $this->refresh_token,
                'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 0),
            ]);

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SSO token refresh failed for user, clearing tokens.', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            // Clear stale tokens so we don't keep trying
            $this->update([
                'access_token' => null,
                'refresh_token' => null,
                'token_expires_at' => null,
            ]);

            return false;
        }
    }

    /**
     * Update user from employee data.
     */
    public function updateFromEmployeeData(array $data): void
    {
        $this->update([
            'employee_code' => $data['NOPEG'] ?? null,
            'name' => $data['NAMA'] ?? $this->name,
            'company' => $data['PERUSAHAAN'] ?? null,
            'unit_id' => $data['ORGANISASI_ID'] ?? null,
            'unit' => $data['ORGANISASI'] ?? null,
            'position_id' => $data['POSISI_ID'] ?? null,
            'position' => $data['POSISI'] ?? null,
            'mobile_phone' => $data['MOBILE_PHONE'] ?? null,
        ]);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'external_id');
    }

    public function unit_model(): ?Unit
    {
        return $this->unit;
    }

    /**
     * Override hasPermissionTo to include unit-level permissions.
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        // 1. Check individual/role permissions first (Spatie default)
        if ($this->parentHasPermissionTo($permission, $guardName)) {
            return true;
        }

        // 2. Check unit-level permissions
        $unit = $this->unit_model();

        return $unit ? $unit->hasPermissionTo($permission, $guardName) : false;
    }

    /**
     * Alias for Spatie's hasPermissionTo to avoid infinite recursion.
     */
    protected function parentHasPermissionTo($permission, $guardName = null): bool
    {
        $permission = $this->filterPermission($permission, $guardName);

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }
}
