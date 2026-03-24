<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

class QrScanController extends Controller
{
    /**
     * Display a public profile of the vehicle owner based on QR sticker ID.
     */
    public function show($qr_sticker_id)
    {
        $registration = Registration::with(['user', 'vehicle'])
            ->where('qr_sticker_id', $qr_sticker_id)
            ->first();

        if (!$registration) {
            abort(404, 'Sticker not found or invalid.');
        }

        return view('scan.show', compact('registration'));
    }
}
