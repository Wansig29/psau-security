<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Violation;
use App\Models\Sanction;
use App\Models\Vehicle;
use Carbon\Carbon;

class ViolationController extends Controller
{
    /**
     * Show the form for creating a new violation.
     */
    public function create(Request $request)
    {
        $vehicle_id = $request->query('vehicle_id');
        $registration_id = $request->query('registration_id');

        $vehicle = null;
        if ($vehicle_id) {
            $vehicle = Vehicle::with('user')->find($vehicle_id);
        }

        return view('security.violation.create', compact('vehicle_id', 'registration_id', 'vehicle'));
    }

    /**
     * Store a newly created violation and automatically apply a sanction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'     => 'required|exists:vehicles,id',
            'registration_id'=> 'nullable|exists:registrations,id',
            'violation_type' => 'required|string',
            'location_notes' => 'required|string',
            'photo_image'    => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'gps_lat'        => 'nullable|numeric',
            'gps_lng'        => 'nullable|numeric',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo_image')) {
            $photoPath = $request->file('photo_image')->store('violations', 'public');
        }

        $currentYear = date('Y') . '-' . (date('Y') + 1);

        // Save the violation record
        $violation = Violation::create([
            'vehicle_id'      => $request->vehicle_id,
            'registration_id' => $request->registration_id,
            'school_year'     => $currentYear,
            'violation_type'  => $request->violation_type,
            'location_notes'  => $request->location_notes,
            'photo_path'      => $photoPath,
            'gps_lat'         => $request->gps_lat,
            'gps_lng'         => $request->gps_lng,
            'logged_by'       => auth()->id(),
            'sanction_applied'=> false,
        ]);

        // ─── Auto-Sanction Logic ───────────────────────────────────────────────
        $today = Carbon::today();
        $sanctionType = 'Suspended';
        $endDate = null;
        $description = '';

        // Special case: unregistered vehicle driven by unlicensed person → 7-day ban
        if ($request->violation_type === 'unregistered_no_license') {
            $endDate     = $today->copy()->addDays(7);
            $description = 'Special offense: Unregistered vehicle + No driver\'s license. Banned from school premises for 7 days.';
        } else {
            // Count how many PRIOR violations this vehicle has (not counting this one)
            $priorCount = Violation::where('vehicle_id', $request->vehicle_id)
                ->where('id', '!=', $violation->id)
                ->count();

            // Offense number = prior violations + 1 (this one)
            $offenseNumber = $priorCount + 1;

            if ($offenseNumber === 1) {
                // 1st offense → 3-day ban
                $endDate     = $today->copy()->addDays(3);
                $description = '1st offense: Banned from school premises for 3 days.';
            } elseif ($offenseNumber === 2) {
                // 2nd offense → 5-day ban
                $endDate     = $today->copy()->addDays(5);
                $description = '2nd offense: Banned from school premises for 5 days.';
            } else {
                // 3rd+ offense → banned for the rest of the semester (end of school year)
                $sanctionType = 'Revoked';
                // End of semester: June of the current school year's end
                $schoolYearEnd = Carbon::create((int)date('Y') + 1, 6, 30);
                $endDate       = $schoolYearEnd;
                $description   = "Offense #{$offenseNumber}: Banned from school premises for the entire semester (until {$endDate->format('M d, Y')}).";
            }
        }

        Sanction::create([
            'vehicle_id'    => $request->vehicle_id,
            'violation_id'  => $violation->id,
            'sanction_type' => $sanctionType,
            'start_date'    => $today,
            'end_date'      => $endDate,
            'is_active'     => true,
            'source'        => 'auto',
            'description'   => $description,
        ]);

        // Mark the violation as sanctioned
        $violation->update(['sanction_applied' => true]);
        // ──────────────────────────────────────────────────────────────────────

        return redirect()->route('security.dashboard')
            ->with('status', 'Violation logged and sanction automatically applied.');
    }
}
