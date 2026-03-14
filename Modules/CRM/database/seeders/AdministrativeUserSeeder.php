<?php

namespace Modules\CRM\Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministrativeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Direksi',
            'VP Business Support',
            'VP Finance',
            'VP Ops',
            'VP Human Capital',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $users = [
            [
                'name' => 'Cornelis Radjawane',
                'role' => 'Direksi',
                'email' => 'cornelis@gdps.id',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Achmad Syifa',
                'role' => 'VP Business Support',
                'email' => 'achmad@gdps.id',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Theresia',
                'role' => 'VP Finance',
                'email' => 'theresia@gdps.id',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Dartin Anton',
                'role' => 'VP Ops',
                'email' => 'dartin@gdps.id',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Wiwied Widyasmara',
                'role' => 'VP Human Capital',
                'email' => 'wiwied@gdps.id',
                'signature_pin' => '123456',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'signature_pin' => Hash::make($userData['signature_pin']),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$userData['role']]);
        }
    }
}
