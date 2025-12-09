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
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@electric.test',
            'role' => 'super_admin',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Super Admin created:');
        $this->command->info('   Email: admin@electric.test');
        $this->command->info('   Password: admin123');
        $this->command->newLine();

        // Create some demo sub-admins
        $admin1 = User::create([
            'name' => 'Admin Khu A',
            'email' => 'admin.khua@electric.test',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
            'created_by' => $superAdmin->id,
            'email_verified_at' => now(),
        ]);

        $admin2 = User::create([
            'name' => 'Admin Khu B',
            'email' => 'admin.khub@electric.test',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
            'created_by' => $superAdmin->id,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Sub-admins created:');
        $this->command->info('   Admin Khu A: admin.khua@electric.test / admin123');
        $this->command->info('   Admin Khu B: admin.khub@electric.test / admin123');
        $this->command->newLine();

        // Assign organizations to sub-admins (example)
        // Admin Khu A quản lý org 1, 23, 46
        if (\App\Models\OrganizationUnit::count() > 0) {
            $admin1->organizationUnits()->attach([
                1 => ['is_primary' => true],
                23 => ['is_primary' => false],
                46 => ['is_primary' => false],
            ]);

            // Admin Khu B quản lý org 57, 68, 76
            $admin2->organizationUnits()->attach([
                57 => ['is_primary' => true],
                68 => ['is_primary' => false],
                76 => ['is_primary' => false],
            ]);

            $this->command->info('✅ Organizations assigned to sub-admins');
        }

        $this->command->newLine();
        $this->command->info('🎉 Setup complete!');
    }
}
