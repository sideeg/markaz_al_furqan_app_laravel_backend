<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Database\Seeders\UserSeeder;
use Database\Seeders\StudentSeeder;
use Database\Seeders\SheikhSeeder;
use Database\Seeders\CourseSeeder;
use Database\Seeders\HifzLogSeeder;
use Database\Seeders\ReviewLogSeeder;
use Database\Seeders\NotificationSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'sheikh', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        
        $this->call([
            UserSeeder::class,
            StudentSeeder::class,
            SheikhSeeder::class,
            CourseSeeder::class,
            HifzLogSeeder::class,
            ReviewLogSeeder::class,
            NotificationSeeder::class,
        ]);
        

        
    }
}
