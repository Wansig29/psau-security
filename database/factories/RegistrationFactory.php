<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Registration;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vehicle = Vehicle::factory()->create();
        $currentYear = date('Y') . '-' . (date('Y') + 1);

        return [
            'user_id' => $vehicle->user_id,
            'vehicle_id' => $vehicle->id,
            'school_year' => $currentYear,
            'status' => 'pending',
            // Default assumes no decision yet
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'qr_sticker_id' => null,
        ];
    }

    /**
     * Indicate that the registration is approved.
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'approved_by' => User::where('role', 'admin')->first()->id ?? User::factory()->admin()->create()->id,
                'approved_at' => now(),
                'qr_sticker_id' => 'PSAU-' . strtoupper(Str::random(7)),
            ];
        });
    }

    /**
     * Indicate that the registration is rejected.
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'rejected_by' => User::where('role', 'admin')->first()->id ?? User::factory()->admin()->create()->id,
                'rejected_at' => now(),
                'rejection_reason' => 'Missing documents or Invalid OR/CR.',
            ];
        });
    }

    /**
     * Indicate that the registration is pending.
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }
}
