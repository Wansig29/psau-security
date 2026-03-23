<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Vehicle;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $makes = ['Toyota', 'Honda', 'Ford', 'Mitsubishi', 'Nissan', 'Hyundai', 'Suzuki', 'Yamaha'];
        
        return [
            'user_id' => User::factory()->user(), // Creates a user if none provided
            'make' => $this->faker->randomElement($makes),
            'model' => $this->faker->word() . ' ' . $this->faker->randomNumber(3),
            'color' => $this->faker->safeColorName(),
            'plate_number' => strtoupper($this->faker->bothify('???-####')),
        ];
    }
}
