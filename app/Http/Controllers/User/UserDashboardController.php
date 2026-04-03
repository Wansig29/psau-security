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

        $approvedVehiclesCount = $vehicles->filter(function ($veh) {
            return $veh->registrations->where('status', 'approved')->isNotEmpty();
        })->count();

        return view('user.dashboard', compact('user', 'vehicles', 'registrations', 'violations', 'approvedVehiclesCount'));
    }

    public function destroyVehicle(\App\Models\Vehicle $vehicle)
    {
        if ($vehicle->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $vehicle->delete();

        return redirect()->route('user.dashboard')->with('status', 'Vehicle removed successfully.');
    }

    public function info()
    {
        $user = auth()->user();
        return view('user.info', compact('user'));
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
