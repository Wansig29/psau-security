<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class QrScanController extends Controller
{
    /**
     * Display a public profile of the vehicle owner based on QR sticker ID or plate number.
     */
    public function show($qr_sticker_id)
    {
        // 🚀 Speed feature: If a logged-in guard or admin scans this, instantly redirect them to the full live map security profile!
        if (auth()->check() && in_array(auth()->user()->role, ['security', 'admin'])) {
            return redirect()->route('security.search', ['query' => $qr_sticker_id]);
        }

        $qrOrPlateClean = str_replace(['-', ' '], '', strtoupper($qr_sticker_id));

        $preferApproved = fn($q) => $q
            ->orderByRaw("CASE WHEN LOWER(status) = 'approved' THEN 0 WHEN LOWER(status) = 'pending' THEN 1 ELSE 2 END")
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at');

        // 1️⃣ Primary: look up by qr_sticker_id (exact, canonical)
        $registration = $preferApproved(
            Registration::with(['user', 'vehicle'])
                ->whereRaw("REPLACE(REPLACE(UPPER(qr_sticker_id), '-', ''), ' ', '') = ?", [$qrOrPlateClean])
        )->first();

        // 2️⃣ Fallback: look up by plate number via the Vehicle table directly
        if (!$registration) {
            $vehicle = Vehicle::whereRaw(
                "REPLACE(REPLACE(UPPER(plate_number), '-', ''), ' ', '') = ?",
                [$qrOrPlateClean]
            )->first();

            if ($vehicle) {
                $registration = $preferApproved(
                    Registration::with(['user', 'vehicle'])
                        ->where('vehicle_id', $vehicle->id)
                )->first();
            }
        }

        if (!$registration) {
            abort(404, 'No vehicle found for this plate number or sticker ID.');
        }

        return view('scan.show', compact('registration'));
    }
}
