<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\PickupSchedule;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AdminApprovedRegistrationController extends Controller
{
    /**
     * Display a listing of approved registrations.
     */
    public function index()
    {
        $approvedRegistrations = Registration::with(['user', 'vehicle', 'pickupSchedule'])
            ->where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->paginate(15);
            
        return view('admin.approved.index', compact('approvedRegistrations'));
    }

    /**
     * Generate and display the printable QR Sticker.
     */
    public function generateQr(Registration $registration)
    {
        if ($registration->status !== 'approved' || !$registration->qr_sticker_id) {
            abort(404, 'No QR Sticker assigned or registration not approved.');
        }

        // URL that the QR code will direct to (e.g. security search page pre-filled with the ID)
        $url = route('security.search') . '?query=' . urlencode($registration->qr_sticker_id);

        // Generate the SVG QR code
        $qrCodeSvg = QrCode::size(250)->generate($url);

        return view('admin.approved.qr-print', compact('registration', 'qrCodeSvg'));
    }
    
    /**
     * Schedule a pick-up time for the user.
     */
    public function schedulePickup(Request $request, Registration $registration)
    {
        $request->validate([
            'pickup_date'  => 'required|date|after_or_equal:today',
            'pickup_time'  => 'required|date_format:H:i',
            'location'     => 'required|string|max:255',
        ]);
        
        PickupSchedule::updateOrCreate(
            ['registration_id' => $registration->id],
            [
                'pickup_date'  => $request->pickup_date,
                'pickup_time'  => $request->pickup_time,
                'location'     => $request->location,
                'is_completed' => false,
            ]
        );
        
        return back()->with('success', 'Pick-up schedule saved successfully.');
    }
    
    /**
     * Mark a sticker as claimed / picked up.
     */
    public function markAsClaimed(Registration $registration)
    {
        $schedule = $registration->pickupSchedule;
        if ($schedule) {
            $schedule->update([
                'is_completed' => true,
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);
            return back()->with('success', 'Sticker marked as claimed!');
        }
        
        return back()->with('error', 'No schedule found for this registration.');
    }
}
