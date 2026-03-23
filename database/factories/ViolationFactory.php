<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Violation;
use App\Models\Vehicle;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Violation>
 */
class ViolationFactory extends Factory
{
    protected $model = Violation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vehicle = Vehicle::factory()->create();
        $currentYear = date('Y') . '-' . (date('Y') + 1);

        $violationTypes = ['no_sticker', 'illegal_parking', 'blocking_driveway', 'speeding', 'reckless_driving', 'other'];

        return [
            'vehicle_id' => $vehicle->id,
            'registration_id' => null, // Can be attached manually if needed
            'school_year' => $currentYear,
            'violation_type' => $this->faker->randomElement($violationTypes),
            'location_notes' => 'Parked near ' . $this->faker->company() . ' building',
            'photo_path' => null,
            'logged_by' => User::where('role', 'security')->first()->id ?? User::factory()->security()->create()->id,
            'sanction_applied' => false,
        ];
    }
}
