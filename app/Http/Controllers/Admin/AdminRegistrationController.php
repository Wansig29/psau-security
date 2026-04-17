<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationDocument;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminRegistrationController extends Controller
{
    /**
     * Approve a vehicle registration.
     */
    public function approve(Request $request, $id)
    {
        try {
            $registration = Registration::with(['vehicle', 'user', 'documents'])->findOrFail($id);

            // Prevent re-approval
            if (strtolower((string) $registration->status) !== 'pending') {
                return back()->with('error', 'Only pending registrations can be approved.');
            }

            // Retry plate extraction at approval if OCR during submission failed, or allow admin override
            $vehicle = $registration->vehicle;
            $corrected = $request->input('corrected_plate');
            if (!empty($corrected) && $corrected !== $vehicle?->plate_number) {
                $vehicle?->update(['plate_number' => strtoupper(trim($corrected))]);
            } else if ($vehicle && $this->isPlaceholderPlate($vehicle->plate_number)) {
                $plateNumber = $this->extractPlateFromRegistrationDocs($registration);
                if ($plateNumber !== null) {
                    $vehicle->update(['plate_number' => $plateNumber]);
                }
            }

            $registration->update([
                'status' => 'Approved',
                'qr_sticker_id' => 'PSAU-' . strtoupper(Str::random(8)),
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            $user = $registration->user;
            if ($user) {
                $message = "Congratulations! Your sticker application for {$registration->vehicle->make} {$registration->vehicle->model} has been APPROVED! Please check your dashboard for scheduling instructions.";
                $user->notify(new \App\Notifications\RegistrationStatusUpdated($registration, 'approved', $message));
            }

            return back()->with('success', 'Registration approved successfully. QR Sticker generated.');
        } catch (\Throwable $e) {
            Log::error('Admin registration approval failed', [
                'registration_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Approval failed. Please try again.');
        }
    }

    /**
     * Reject a vehicle registration.
     */
    public function reject(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);

        if (strtolower((string) $registration->status) !== 'pending') {
            return back()->with('error', 'Only pending registrations can be rejected.');
        }

        // We can add validation for a rejection reason later if needed
        $registration->update([
            'status' => 'Rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $request->input('reason', 'Document details do not match or are invalid.'),
        ]);

        $user = $registration->user;
        $reason = $registration->rejection_reason;
        $message = "Your sticker application for {$registration->vehicle->make} {$registration->vehicle->model} was REJECTED. Reason: {$reason}";
        $user->notify(new \App\Notifications\RegistrationStatusUpdated($registration, 'rejected', $message));

        return back()->with('success', 'Registration rejected.');
    }

    private function isPlaceholderPlate(?string $plate): bool
    {
        $p = strtoupper(trim((string) $plate));
        return $p === '' || str_starts_with($p, 'UNKNOWN_') || str_starts_with($p, 'PENDING_');
    }

    private function extractPlateFromRegistrationDocs(Registration $registration): ?string
    {
        $documents = $registration->documents->keyBy('document_type');

        $ocrSources = [];
        if (!empty($documents['vehicle_photo']?->ocr_extracted_text)) {
            $ocrSources[] = $documents['vehicle_photo']->ocr_extracted_text;
        }
        if (!empty($documents['or']?->ocr_extracted_text)) {
            $ocrSources[] = $documents['or']->ocr_extracted_text;
        }
        if (!empty($documents['cr']?->ocr_extracted_text)) {
            $ocrSources[] = $documents['cr']->ocr_extracted_text;
        }

        foreach ($ocrSources as $text) {
            if (preg_match('/([A-Z]{2,3}[\s-]?[0-9]{3,4}|[0-9]{3,4}[\s-]?[A-Z]{2,3})/', strtoupper((string) $text), $matches)) {
                return str_replace([' ', '-'], '', $matches[0]);
            }
        }

        // Fallback: run OCR directly from OR then CR image files at approval time.
        foreach (['or', 'cr', 'vehicle_photo'] as $type) {
            /** @var RegistrationDocument|null $doc */
            $doc = $documents[$type] ?? null;
            if (!$doc || empty($doc->image_path)) {
                continue;
            }

            try {
                $fullPath = storage_path('app/public/' . $doc->image_path);
                if (!is_file($fullPath)) {
                    continue;
                }
                $ocrText = (new \thiagoalessio\TesseractOCR\TesseractOCR($fullPath))->run();
                if (preg_match('/([A-Z]{2,3}[\s-]?[0-9]{3,4}|[0-9]{3,4}[\s-]?[A-Z]{2,3})/', strtoupper($ocrText), $matches)) {
                    return str_replace([' ', '-'], '', $matches[0]);
                }
            } catch (\Throwable $e) {
                Log::warning('Approval OCR retry failed', [
                    'registration_id' => $registration->id,
                    'document_type' => $type,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Provide the document image directly from the database LONGBLOB instead of the filesystem.
     */
    public function showImage($id)
    {
        $document = RegistrationDocument::findOrFail($id);

        if (empty($document->image_data)) {
            // Fallback to local disk if image_data is empty (e.g., from old registrations)
            if (!empty($document->image_path)) {
                $fullPath = storage_path('app/public/' . $document->image_path);
                if (file_exists($fullPath)) {
                    return response()->file($fullPath);
                }
            }
            abort(404, 'Image not found');
        }

        return response($document->image_data)->header('Content-Type', 'image/jpeg');
    }
}
