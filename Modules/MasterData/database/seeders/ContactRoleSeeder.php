<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\ContactRole;

class ContactRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'PIC Customer',
            'PIC Finance',
            'PIC Technical',
            'PIC Legal',
            'Project Manager',
            'Site Manager',
            'Admin',
        ];

        foreach ($roles as $role) {
            ContactRole::firstOrCreate(['name' => $role]);
        }
    }
}
