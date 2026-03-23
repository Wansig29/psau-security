<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Violation;
use App\Models\Sanction;
use Illuminate\Http\Request;

class AdminSanctionController extends Controller
{
    /**
     * Display a listing of violations that need sanctions.
     */
    public function index()
    {
        $violations = Violation::with(['vehicle.user', 'sanctions', 'loggedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.sanctions.index', compact('violations'));
    }

    /**
     * Save a new sanction for a violation.
     */
    public function store(Request $request, Violation $violation)
    {
        $request->validate([
            'sanction_type' => 'required|in:Warning,Suspended,Revoked',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
        ]);

        Sanction::create([
            'vehicle_id'    => $violation->vehicle_id,
            'violation_id'  => $violation->id,
            'sanction_type' => $request->sanction_type,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'is_active'     => true,
            'source'        => 'manual',
        ]);
        
        // Mark violation as sanctioned
        $violation->update(['sanction_applied' => true]);

        return back()->with('success', 'Sanction assigned successfully.');
    }
    
    /**
     * Deactivate a sanction (e.g., served / lifted).
     */
    public function resolve(Sanction $sanction)
    {
        $sanction->update(['is_active' => false]);
        
        return back()->with('success', 'Sanction marked as resolved/lifted.');
    }
}
