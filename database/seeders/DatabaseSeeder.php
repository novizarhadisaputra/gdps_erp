<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'rajabannisa.wahyuni@garudapratama.com'],
            [
                'name' => 'System Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('gdps2019!'),
                'signature_pin' => \Illuminate\Support\Facades\Hash::make('123456'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            ShieldSeeder::class,
            \Modules\MasterData\Database\Seeders\MasterDataDatabaseSeeder::class,
            // \Modules\CRM\Database\Seeders\CRMDatabaseSeeder::class,
            // \Modules\Project\Database\Seeders\ProjectDatabaseSeeder::class,
        ]);

        $admin = User::query()->where('email', '=', 'rajabannisa.wahyuni@garudapratama.com')->first();
        if ($admin) {
            $admin->assignRole('super_admin');
        }
    }
}
