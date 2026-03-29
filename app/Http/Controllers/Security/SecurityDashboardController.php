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

        $mapViolations = \App\Models\Violation::with(['vehicle.user'])
            ->whereNotNull('gps_lat')
            ->latest()
            ->take(50)
            ->get()
            ->append([]) // keep as collection
            ->map(function ($v) {
                $data = $v->toArray();
                // Attach the vehicle owner's live GPS
                if ($v->vehicle && $v->vehicle->user) {
                    $u = $v->vehicle->user;
                    $data['owner_lat']  = $u->current_lat;
                    $data['owner_lng']  = $u->current_lng;
                    $data['owner_last_seen'] = $u->last_location_update
                        ? $u->last_location_update->format('M d, Y g:i A')
                        : null;
                    $data['owner_online'] = $u->last_location_update
                        && $u->last_location_update->diffInMinutes(now()) <= 5;
                } else {
                    $data['owner_lat']  = null;
                    $data['owner_lng']  = null;
                    $data['owner_last_seen'] = null;
                    $data['owner_online'] = false;
                }
                return $data;
            });

        return view('security.dashboard', compact('recentViolations', 'mapViolations'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (!$query) {
            return back()->with('error', 'Please enter a search term.');
        }

        // If the query is a full URL from scanning the QR code, extract the last segment
        if (filter_var($query, FILTER_VALIDATE_URL)) {
            $path = parse_url($query, PHP_URL_PATH); // e.g., '/scan/QR-001' or '/scan/HJW-0827'
            if ($path) {
                $query = basename($path);
            }
        }

        $queryClean = str_replace(['-', ' '], '', strtoupper($query));

        // Search in Vehicle Plate Number or Registration QR Sticker ignoring case, spaces, and hyphens
        $vehicle = \App\Models\Vehicle::with(['registrations' => function($q) {
            // Prefer approved over pending so the security card shows "valid entry" when available.
            // Also uses LOWER(status) to handle DB enum values like "Approved"/"Pending".
            $q->orderByRaw("CASE
                WHEN LOWER(status) = 'approved' THEN 0
                WHEN LOWER(status) = 'pending' THEN 1
                ELSE 2
            END")
                ->orderByDesc('approved_at')
                ->orderByDesc('created_at')
                ->limit(1);
        }, 'user'])
        ->whereRaw("REPLACE(REPLACE(UPPER(plate_number), '-', ''), ' ', '') LIKE ?", ["%{$queryClean}%"])
        ->orWhereHas('registrations', function ($q) use ($queryClean) {
            $q->whereRaw("REPLACE(REPLACE(UPPER(qr_sticker_id), '-', ''), ' ', '') LIKE ?", ["%{$queryClean}%"]);
        })->first();

        if (!$vehicle) {
            return back()->with('error', "No vehicle found matching '{$query}'.");
        }

        return view('security.search-result', compact('vehicle'));
    }

    public function getUserLocation(\App\Models\User $user)
    {
        return response()->json([
            'lat' => $user->current_lat,
            'lng' => $user->current_lng,
            'last_update' => $user->last_location_update ? $user->last_location_update->diffForHumans() : null,
            'last_seen_time' => $user->last_location_update ? $user->last_location_update->format('M d, Y g:i A') : null,
            'is_online' => $user->last_location_update && $user->last_location_update->diffInMinutes(now()) <= 5
        ]);
    }
}
