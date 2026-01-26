<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
    ];

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
}
