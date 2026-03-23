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
        // 1. Create Core Platform Users
        $admin = User::factory()->admin()->create([
            'name' => 'Campus Admin',
            'email' => 'admin@psau.edu.ph',
            'password' => bcrypt('password'),
        ]);

        $security = User::factory()->security()->create([
            'name' => 'Officer Dela Cruz',
            'email' => 'security@psau.edu.ph',
            'password' => bcrypt('password'),
        ]);

        $user = User::factory()->user()->create([
            'name' => 'Juan Student',
            'email' => 'user@psau.edu.ph',
            'password' => bcrypt('password'),
        ]);

        // Give the standard test user a vehicle with an approved registration
        $userVehicle = \App\Models\Vehicle::factory()->create(['user_id' => $user->id]);
        \App\Models\Registration::factory()->approved()->create(['user_id' => $user->id, 'vehicle_id' => $userVehicle->id]);
        \App\Models\RegistrationDocument::factory()->create(['registration_id' => $userVehicle->registrations->first()->id]);

        // 2. Generate Random Vehicle Users with various Registration States
        User::factory()->user()->count(5)->create()->each(function ($u) {
            // Each gets 1-2 vehicles
            $vehicles = \App\Models\Vehicle::factory()->count(rand(1, 2))->create(['user_id' => $u->id]);
            
            foreach ($vehicles as $v) {
                // Randomly assign it an approved, pending, or rejected registration
                $state = fake()->randomElement(['approved', 'pending', 'rejected']);
                
                $reg = \App\Models\Registration::factory()->{$state}()->create([
                    'user_id' => $u->id,
                    'vehicle_id' => $v->id
                ]);

                // Attach a document
                \App\Models\RegistrationDocument::factory()->create(['registration_id' => $reg->id]);
            }
        });

        // 3. Generate Random Violations
        // Get some random vehicles that were generated above
        $randomVehicles = \App\Models\Vehicle::inRandomOrder()->take(5)->get();
        
        foreach ($randomVehicles as $v) {
            \App\Models\Violation::factory()->create([
                'vehicle_id' => $v->id,
                'logged_by' => $security->id, // Logged by our test officer
            ]);
        }
    }
}
