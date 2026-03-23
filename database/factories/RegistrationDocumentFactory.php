<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RegistrationDocument;
use App\Models\Registration;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegistrationDocument>
 */
class RegistrationDocumentFactory extends Factory
{
    protected $model = RegistrationDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'document_type' => 'CR',
            'image_path' => 'dummy/or_cr_path.jpg',
            'ocr_extracted_text' => 'Dummy OCR Text extracted from OR/CR: ' . $this->faker->text(50),
            'match_score' => 95,
            'is_flagged' => false,
            'flagged_fields' => [],
        ];
    }
}
