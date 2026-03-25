<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $pendingRegistrations = \App\Models\Registration::with(['user', 'vehicle', 'documents'])
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->latest()
            ->get();

        return view('admin.dashboard', compact('pendingRegistrations'));
    }
}
