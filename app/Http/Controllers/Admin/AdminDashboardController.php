<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $pendingRegistrations = \App\Models\Registration::with(['user', 'vehicle', 'documents'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        // Get all violations that have GPS coordinates for the map
        $mapViolations = \App\Models\Violation::with('vehicle')
            ->whereNotNull('gps_lat')
            ->whereNotNull('gps_lng')
            ->get();

        // Get aggregated statistics for Chart.js
        $violationStats = \App\Models\Violation::select('violation_type', DB::raw('count(*) as total'))
            ->groupBy('violation_type')
            ->pluck('total', 'violation_type');

        return view('admin.dashboard', compact('pendingRegistrations', 'mapViolations', 'violationStats'));
    }
}
