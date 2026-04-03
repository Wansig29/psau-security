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
        try {
            $request->validate([
                'contact_number' => ['nullable', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'make'          => 'required|string|max:255',
                'model'         => 'required|string|max:255',
                'color'         => 'required|string|max:255',
                'doc_or'        => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:5120',
                'doc_cr'        => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:5120',
                'doc_cor'       => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:5120',
                'doc_license'   => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:5120',
                'doc_school_id' => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:5120',
            ]);

            $user = $request->user();
            // Capture/update the owner's phone number so security can tap-to-call.
            if ($request->filled('contact_number')) {
                $user->update([
                    'contact_number' => $request->input('contact_number'),
                ]);
            }

            // Helper: compress and store an uploaded image
            $storeAndCompress = function ($file, $folder) {
                $path     = $file->store($folder, 'public');
                $fullPath = storage_path('app/public/' . $path);
                try {
                    $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                    $img     = $manager->read($fullPath);
                    if ($img->width() > 1200) { $img->scale(width: 1200); }
                    $img->toJpeg(70)->save($fullPath);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Image compression failed: ' . $e->getMessage());
                }
                return ['path' => $path, 'full' => $fullPath];
            };

            // 1. Store all five documents
            $docs = [
                'or'        => $storeAndCompress($request->file('doc_or'),        'registrations/or'),
                'cr'        => $storeAndCompress($request->file('doc_cr'),        'registrations/cr'),
                'cor'       => $storeAndCompress($request->file('doc_cor'),       'registrations/cor'),
                'license'   => $storeAndCompress($request->file('doc_license'),   'registrations/license'),
                'school_id' => $storeAndCompress($request->file('doc_school_id'), 'registrations/school_id'),
            ];

            // 2. Run OCR on the OR document to extract the plate number
            $ocrText     = '';
            $plateNumber = 'UNKNOWN_' . \Illuminate\Support\Str::random(8);
            try {
                $ocrText = (new \thiagoalessio\TesseractOCR\TesseractOCR($docs['or']['full']))->run();
                if (preg_match('/[A-Z]{3}[\s-]?[0-9]{3,4}/', strtoupper($ocrText), $matches)) {
                    $plateNumber = str_replace([' ', '-'], '', $matches[0]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('OCR failed on OR document: ' . $e->getMessage());
            }

            // 3. Create Vehicle
            $vehicle = \App\Models\Vehicle::create([
                'user_id'      => $user->id,
                'plate_number' => $plateNumber,
                'make'         => $request->make,
                'model'        => $request->model,
                'color'        => $request->color,
            ]);

            // 4. Create Registration
            $currentYear  = date('Y') . '-' . (date('Y') + 1);
            $registration = \App\Models\Registration::create([
                'user_id'     => $user->id,
                'vehicle_id'  => $vehicle->id,
                'school_year' => $currentYear,
                'status'      => 'pending',
            ]);

            // 5. Save each document record
            $docTypes = [
                'or'        => ['type' => 'or',        'ocr' => $ocrText, 'flagged' => str_starts_with($plateNumber, 'UNKNOWN_') ? ['plate_number' => 'Not found in OCR'] : null],
                'cr'        => ['type' => 'cr',        'ocr' => null, 'flagged' => null],
                'cor'       => ['type' => 'cor',       'ocr' => null, 'flagged' => null],
                'license'   => ['type' => 'license',   'ocr' => null, 'flagged' => null],
                'school_id' => ['type' => 'school_id', 'ocr' => null, 'flagged' => null],
            ];

            foreach ($docTypes as $key => $meta) {
                \App\Models\RegistrationDocument::create([
                    'registration_id'   => $registration->id,
                    'document_type'     => $meta['type'],
                    'image_path'        => $docs[$key]['path'],
                    'ocr_extracted_text'=> $meta['ocr'],
                    'match_score'       => 0,
                    'flagged_fields'    => $meta['flagged'],
                ]);
            }

            return redirect()->route('user.dashboard')->with('status', 'Registration submitted! It is now pending admin review.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation errors so they display on the form normally
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vehicle registration failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Server error: ' . $e->getMessage());
        }
    }
}
