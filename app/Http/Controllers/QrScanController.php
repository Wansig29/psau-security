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
        // 🚀 Speed feature: If a logged-in guard or admin scans this, instantly redirect them to the full live map security profile!
        if (auth()->check() && in_array(auth()->user()->role, ['security', 'admin'])) {
            return redirect()->route('security.search', ['query' => $qr_sticker_id]);
        }

        $registration = Registration::with(['user', 'vehicle'])
            ->whereRaw('LOWER(qr_sticker_id) = ?', [strtolower($qr_sticker_id)])
            ->first();

        if (!$registration) {
            abort(404, 'Sticker not found or invalid.');
        }

        return view('scan.show', compact('registration'));
    }
}
