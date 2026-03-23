<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SecurityDashboardController extends Controller
{
    public function index()
    {
        $recentViolations = \App\Models\Violation::with(['vehicle', 'registration'])
            ->where('logged_by', auth()->id())
            ->latest()
            ->take(10)
            ->get();

        $mapViolations = \App\Models\Violation::with(['vehicle'])->whereNotNull('gps_lat')->latest()->take(50)->get();

        return view('security.dashboard', compact('recentViolations', 'mapViolations'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (!$query) {
            return back()->with('error', 'Please enter a search term.');
        }

        // Search in Vehicle Plate Number or Registration QR Sticker
        $vehicle = \App\Models\Vehicle::with(['registrations' => function($q) {
            $q->latest()->limit(1); // Get the most recent registration config
        }, 'user'])->where('plate_number', 'like', "%{$query}%")
        ->orWhereHas('registrations', function ($q) use ($query) {
            $q->where('qr_sticker_id', 'like', "%{$query}%");
        })->first();

        if (!$vehicle) {
            return back()->with('error', "No vehicle found matching '{$query}'.");
        }

        return view('security.search-result', compact('vehicle'));
    }
}
