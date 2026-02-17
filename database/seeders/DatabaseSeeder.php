<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Database\Seeders\MasterDataDatabaseSeeder;

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
                'password' => Hash::make('gdps2019!'),
                'signature_pin' => Hash::make('123456'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            ShieldSeeder::class,
            MasterDataDatabaseSeeder::class,
        ]);

        $admin = User::query()->where('email', '=', 'rajabannisa.wahyuni@garudapratama.com')->first();
        if ($admin) {
            $admin->assignRole('super_admin');
        }
    }
}
