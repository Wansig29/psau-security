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
        // ── 1-vehicle-per-user guard ──────────────────────────────────────────
        $existingReg = \App\Models\Registration::where('user_id', $request->user()->id)
            ->whereRaw("LOWER(status) IN ('pending', 'approved')")
            ->first();

        if ($existingReg) {
            $status = strtolower((string) $existingReg->status);
            if ($status === 'pending') {
                return redirect()->route('user.dashboard')
                    ->with('error', 'You already have a pending vehicle registration under review. Please wait for admin approval.');
            }
            return redirect()->route('user.vehicle-change.create')
                ->with('error', 'You already have an approved vehicle. To change your vehicle, please submit a Vehicle Change Request below.');
        }

        try {
            $request->validate([
                'contact_number' => ['nullable', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'make'          => 'required|string|max:255',
                'model'         => 'required|string|max:255',
                'color'         => 'required|string|max:255',
                'doc_vehicle_photo' => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:5120',
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
                $data = file_exists($fullPath) ? file_get_contents($fullPath) : null;
                return ['path' => $path, 'full' => $fullPath, 'data' => $data];
            };

            // 1. Store all six documents
            $docs = [
                'vehicle_photo' => $storeAndCompress($request->file('doc_vehicle_photo'), 'registrations/vehicle'),
                'or'            => $storeAndCompress($request->file('doc_or'),        'registrations/or'),
                'cr'            => $storeAndCompress($request->file('doc_cr'),        'registrations/cr'),
                'cor'           => $storeAndCompress($request->file('doc_cor'),       'registrations/cor'),
                'license'       => $storeAndCompress($request->file('doc_license'),   'registrations/license'),
                'school_id'     => $storeAndCompress($request->file('doc_school_id'), 'registrations/school_id'),
            ];

            // 2. Enhanced OCR — multi-variant pre-processing for crumpled/real-world documents
            $ocrText        = '';
            $plateNumber    = null;
            $plateCandidates = [];  // Collect candidates from all docs then vote

            // ── Philippine plate regex ──────────────────────────────────────
            // Covers: ABC1234 · ABC 1234 · AB1234 · ZA0240 · AB1234C · AB-1234
            $plateRegex = '/\b([A-Z]{2,3}[\s\-]?[0-9]{3,4}[A-Z]?)\b/';

            // ── Build multiple pre-processed variants of one source image ──
            // Crumpled or bent documents benefit from different contrast/brightness
            // combos because lighting is uneven. We generate up to 3 variants:
            //   Variant 1 – grayscale + high contrast + sharpen
            //   Variant 2 – grayscale + extreme brightness + binarise (B&W) for dark crumple shadows
            //   Variant 3 – grayscale + contrast + inverted B&W (for light-on-dark plates)
            $buildVariants = function (string $sourcePath): array {
                $variants = [];
                try {
                    $manager = new \Intervention\Image\ImageManager(
                        new \Intervention\Image\Drivers\Gd\Driver()
                    );

                    // ── Variant 1: Classic high-contrast sharpened ────────
                    $img1 = $manager->read($sourcePath);
                    if ($img1->width() < 1800) { $img1->scale(width: 1800); }
                    $img1->greyscale()->contrast(65)->sharpen(20)->brightness(10);
                    $p1 = sys_get_temp_dir() . '/ocr_v1_' . uniqid() . '.jpg';
                    $img1->toJpeg(95)->save($p1);
                    $variants[] = $p1;

                    // ── Variant 2: Strong brighten → binarise (shadow killer) ─
                    $img2 = $manager->read($sourcePath);
                    if ($img2->width() < 1800) { $img2->scale(width: 1800); }
                    $img2->greyscale()->brightness(45)->contrast(80)->sharpen(25);
                    $p2 = sys_get_temp_dir() . '/ocr_v2_' . uniqid() . '.jpg';
                    $img2->toJpeg(95)->save($p2);
                    $variants[] = $p2;

                    // ── Variant 3: Inverted for light-on-dark plates ──────
                    $img3 = $manager->read($sourcePath);
                    if ($img3->width() < 1800) { $img3->scale(width: 1800); }
                    $img3->greyscale()->contrast(70)->sharpen(15)->invert();
                    $p3 = sys_get_temp_dir() . '/ocr_v3_' . uniqid() . '.jpg';
                    $img3->toJpeg(95)->save($p3);
                    $variants[] = $p3;

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('OCR variant build failed: ' . $e->getMessage());
                }
                return $variants;
            };

            // ── Multi-mode Tesseract runner ────────────────────────────────
            // PSM 8  = single word            ← best for plate-only crops
            // PSM 7  = single line of text    ← good for horizontal plate bands
            // PSM 6  = uniform block of text  ← good for full-page OR/CR docs
            // PSM 13 = raw line               ← fallback, ignores all layout
            $extractPlate = function (string $imagePath) use (&$ocrText, $plateRegex): ?string {
                $psmModes = [8, 7, 6, 13];
                foreach ($psmModes as $psm) {
                    try {
                        $ocr = (new \thiagoalessio\TesseractOCR\TesseractOCR($imagePath))
                            ->psm($psm)
                            ->oem(3)
                            ->allowlist('ABCDEFGHJKLMNPQRSTUVWXYZ0123456789 -')
                            ->run();
                        $upper = strtoupper(trim($ocr));
                        $ocrText .= " [psm{$psm}:{$upper}]";
                        if (preg_match($plateRegex, $upper, $m)) {
                            $clean = strtoupper(preg_replace('/[\s\-]/', '', $m[1]));
                            // Sanity-check: must be 5–7 chars long (e.g. ZA0240 or ABC1234)
                            if (strlen($clean) >= 5 && strlen($clean) <= 7) {
                                return $clean;
                            }
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("OCR PSM{$psm} failed: " . $e->getMessage());
                    }
                }
                return null;
            };

            // ── Scan OR + CR + Vehicle Photo, 3 variants each ─────────────
            $docsToScan = [
                'or'            => 'OR',
                'cr'            => 'CR',
                'vehicle_photo' => 'Vehicle Photo',
            ];

            foreach ($docsToScan as $docKey => $docLabel) {
                $sourcePath = $docs[$docKey]['full'];
                $variants   = $buildVariants($sourcePath);

                // If variant build failed, fall back to raw source
                if (empty($variants)) { $variants = [$sourcePath]; }

                foreach ($variants as $variantPath) {
                    $candidate = $extractPlate($variantPath);
                    if ($candidate) {
                        $plateCandidates[] = $candidate;
                        $ocrText .= "\n--- {$docLabel}: matched [{$candidate}] ---\n";
                    }
                    if ($variantPath !== $sourcePath && file_exists($variantPath)) {
                        @unlink($variantPath);
                    }
                }
            }

            // Pick the plate that appeared most across all variants/docs
            if (!empty($plateCandidates)) {
                $counted = array_count_values($plateCandidates);
                arsort($counted); // most frequent first
                foreach (array_keys($counted) as $candidate) {
                    if (!\App\Models\Vehicle::where('plate_number', $candidate)->exists()) {
                        $plateNumber = $candidate;
                        break;
                    }
                    \Illuminate\Support\Facades\Log::info("OCR plate {$candidate} already exists — trying next.");
                }
                // If ALL candidates already exist (rare), just pick the most frequent
                if (!$plateNumber) {
                    $plateNumber = array_key_first($counted);
                }
            }


            // If OCR couldn't find a unique plate, use a unique pending placeholder
            // Admin will manually verify and update it from the submitted documents
            if (!$plateNumber) {
                do {
                    $plateNumber = 'PENDING_' . strtoupper(\Illuminate\Support\Str::random(6));
                } while (\App\Models\Vehicle::where('plate_number', $plateNumber)->exists());
            }

            // Railway's TCP proxy drops idle MySQL connections after 60 seconds.
            // Since the OCR process above can take 20-30 seconds, we force Laravel
            // to reconnect the database before inserting to prevent "MySQL server has gone away".
            \Illuminate\Support\Facades\DB::reconnect();

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
                'vehicle_photo' => ['type' => 'vehicle_photo', 'ocr' => $ocrText, 'flagged' => str_starts_with($plateNumber, 'UNKNOWN_') ? ['plate_number' => 'Not found in OCR'] : null],
                'or'            => ['type' => 'or',            'ocr' => null, 'flagged' => null],
                'cr'            => ['type' => 'cr',            'ocr' => null, 'flagged' => null],
                'cor'           => ['type' => 'cor',           'ocr' => null, 'flagged' => null],
                'license'       => ['type' => 'license',       'ocr' => null, 'flagged' => null],
                'school_id'     => ['type' => 'school_id',     'ocr' => null, 'flagged' => null],
            ];
            foreach ($docTypes as $key => $meta) {
                \App\Models\RegistrationDocument::create([
                    'registration_id'   => $registration->id,
                    'document_type'     => $meta['type'],
                    'image_path'        => $docs[$key]['path'],
                    'image_data'        => $docs[$key]['data'],
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
