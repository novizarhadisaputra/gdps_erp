<?php

namespace Modules\CRM\Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Models\Unit;

class AdministrativeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Board of Directors',
            'VP Business Support',
            'VP Finance',
            'VP Operations',
            'VP Human Capital',
            'Account Manager & Sales',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $users = [
            [
                'name' => 'Cornelis Radjawane',
                'role' => 'Board of Directors',
                'email' => 'cornelis@garudapratama.com',
                'employee_code' => '9500001',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Achmad Syifa',
                'role' => 'VP Business Support',
                'email' => 'a.syifa@garudapratama.com',
                'employee_code' => '9500159',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Theresia',
                'role' => 'VP Finance',
                'email' => 'theresia@garudapratama.com',
                'employee_code' => '9500232',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Dartin Anton',
                'role' => 'VP Operations',
                'email' => 'd.anton@garudapratama.com',
                'employee_code' => '9500060',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Wiwied Widyasmara Adi',
                'role' => 'VP Human Capital',
                'email' => 'wiwied@garudapratama.com',
                'employee_code' => '9500184',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Rajabannisa Airo Wahyuni',
                'role' => 'Account Manager & Sales',
                'email' => 'rajabannisa.wahyuni@garudapratama.com',
                'employee_code' => '9500099',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Faisal Jamil',
                'role' => 'Account Manager & Sales',
                'email' => 'faisal.jamil@garudapratama.com',
                'employee_code' => '9500063',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Rizka Nugraha Wahyudi',
                'role' => 'Account Manager & Sales',
                'email' => 'rizka@garudapratama.com',
                'employee_code' => '9500093',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Budi Darmawan Susilo',
                'role' => 'Account Manager & Sales',
                'email' => 'budi.darmawan@garudapratama.com',
                'employee_code' => '9500276',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Ella Apriska Shintia Dewi',
                'role' => 'Account Manager & Sales',
                'email' => 'ella.apriska@garudapratama.com',
                'employee_code' => '9710050',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Nurul Siti Aulia',
                'role' => 'VP Finance',
                'email' => 'nurul@garudapratama.com',
                'employee_code' => '9500073',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Alvino Richardo Ali',
                'role' => 'VP Finance',
                'email' => 'alvino@garudapratama.com',
                'employee_code' => '9500085',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Nano Wilda Khusnata',
                'role' => 'VP Operations',
                'email' => 'nano.wilda@garudapratama.com',
                'employee_code' => '9500192',
                'signature_pin' => '123456',
            ],
            [
                'name' => 'Agus Raharjo',
                'role' => 'VP Operations',
                'email' => 'agusraharjo@garudapratama.com',
                'employee_code' => '9500155',
                'signature_pin' => '123456',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'employee_code' => $userData['employee_code'],
                    'password' => Hash::make('gdps2019!'),
                    'signature_pin' => Hash::make($userData['signature_pin']),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$userData['role']]);

            // Assign Unit based on Role
            $unitSearch = match ($userData['role']) {
                'VP Business Support' => 'Business Support',
                'VP Finance' => 'Finance',
                'VP Operations' => 'Operations',
                'VP Human Capital' => 'Human Capital',
                'Board of Directors' => 'Board of Director',
                'Account Manager & Sales' => 'Business Support',
                default => null,
            };

            if ($unitSearch) {
                $unit = Unit::where('name', 'like', "%{$unitSearch}%")->first();
                if ($unit) {
                    $user->update([
                        'unit_id' => $unit->external_id,
                        'unit' => $unit->name,
                    ]);
                }
            }
        }
    }
}
