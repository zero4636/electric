<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create only Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@electric.test'],
            [
                'name' => 'Super Admin',
                'role' => 'super_admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Super Admin created:');
        $this->command->info('   Email: admin@electric.test');
        $this->command->info('   Password: admin123');
        $this->command->newLine();
        $this->command->info('🎉 Setup complete!');
    }
}
