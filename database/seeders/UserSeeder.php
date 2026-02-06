<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Create admin user
         $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@markaz.com',
            'password' => Hash::make('123456'), // Change this password
            'phone' => '+966500000000',
            'national_id' => '1111111111',
            // 'qiraat' => 'Hafs',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $admin2 = User::create([
            'name' => 'Osama Alsir',
            'email' => 'osama@markaz.com',
            'password' => Hash::make('123456'), // Change this password
            'phone' => '+96650454533',
            'national_id' => '1112211111',
            // 'qiraat' => 'Hafs',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create or get 'admin' role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Assign role to user
        $admin->assignRole($adminRole);
        $admin2->assignRole($adminRole);
        
        // Optional: Create additional users
        // User::factory()->count(10)->create(); 
    }
}
