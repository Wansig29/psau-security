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
        $registrations = $user->registrations()->with(['vehicle', 'documents'])->latest()->get();
        // Use the newly created relationship to load violations linked to user's vehicles
        $violations = $user->violations()->with('vehicle')->latest()->get();

        return view('user.dashboard', compact('vehicles', 'registrations', 'violations', 'user'));
    }
}
