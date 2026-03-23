<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('user.registration.create');
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'photo_path' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'document_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'cor_image' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $user = $request->user();

        // 1. Store the uploaded images
        $vehiclePhotoPath = $request->file('photo_path')->store('vehicles', 'public');
        $orcrImagePath = $request->file('document_image')->store('registrations', 'public');
        $corImagePath = $request->file('cor_image')->store('registrations', 'public');
        
        $fullOrcrImagePath = storage_path('app/public/' . $orcrImagePath);

        // 2. Perform OCR on the OR/CR image (fail gracefully if Tesseract not installed)
        $ocrText = '';
        try {
            $ocrText = (new \thiagoalessio\TesseractOCR\TesseractOCR($fullOrcrImagePath))
                ->run();
        } catch (\Exception $e) {
            // Tesseract not installed or failed — admin will manually verify the document
            \Illuminate\Support\Facades\Log::warning('OCR failed during registration: ' . $e->getMessage());
        }

        // 3. Extract Plate Number (Basic PH format regex: 3 letters, 3/4 numbers)
        // Matches ABC 123, ABC 1234, ABC-123, ABC-1234
        $plateNumber = 'UNKNOWN';
        if (preg_match('/[A-Z]{3}[\s-]?[0-9]{3,4}/', strtoupper($ocrText), $matches)) {
            $plateNumber = str_replace([' ', '-'], '', $matches[0]); // Normalize to ABC1234 format
        }

        // 4. Create Vehicle (Or find if user already registered it... this assumes fresh)
        $vehicle = \App\Models\Vehicle::create([
            'user_id' => $user->id,
            'plate_number' => $plateNumber,
            'make' => $request->make,
            'model' => $request->model,
            'color' => $request->color,
            'photo_path' => $vehiclePhotoPath,
        ]);

        // 5. Create Registration (Assume current school year roughly matches current year)
        $currentYear = date('Y') . '-' . (date('Y') + 1);
        $registration = \App\Models\Registration::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'school_year' => $currentYear,
            'status' => 'pending',
        ]);

        // 6. Save OR/CR Document reference and OCR raw text
        \App\Models\RegistrationDocument::create([
            'registration_id' => $registration->id,
            'document_type' => 'OR/CR',
            'image_path' => $orcrImagePath,
            'ocr_extracted_text' => $ocrText,
            'match_score' => 0, // Admin will manually review this
            'flagged_fields' => $plateNumber === 'UNKNOWN' ? ['plate_number' => 'Not found in OCR'] : null,
        ]);

        // 7. Save COR / Student ID Document reference
        \App\Models\RegistrationDocument::create([
            'registration_id' => $registration->id,
            'document_type' => 'COR',
            'image_path' => $corImagePath,
            'ocr_extracted_text' => null, // Not currently running OCR on COR
            'match_score' => 0,
            'flagged_fields' => null,
        ]);

        return redirect()->route('dashboard')->with('status', 'Registration submitted successfully! It is now pending admin review.');
    }
}
