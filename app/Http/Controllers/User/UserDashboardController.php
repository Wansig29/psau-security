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

        return view('user.dashboard', compact('vehicles', 'registrations'));
    }
}
