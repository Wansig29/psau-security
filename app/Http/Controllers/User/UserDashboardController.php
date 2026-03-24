<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $vehicles = $user->vehicles;
        $registrations = $user->registrations()->with(['vehicle', 'pickupSchedule', 'documents'])->latest()->get();
        $violations = \App\Models\Violation::whereIn('vehicle_id', $vehicles->pluck('id'))->with(['vehicle', 'sanctions'])->latest()->get();

        return view('user.dashboard', compact('user', 'vehicles', 'registrations', 'violations'));
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $user = auth()->user();
        $user->update([
            'current_lat' => $request->lat,
            'current_lng' => $request->lng,
            'last_location_update' => now(),
        ]);

        return response()->json(['status' => 'success']);
    }
}
