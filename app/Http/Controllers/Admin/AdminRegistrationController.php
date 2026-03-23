<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminRegistrationController extends Controller
{
    /**
     * Approve a vehicle registration.
     */
    public function approve(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);
        
        // Prevent re-approval
        if ($registration->status !== 'pending') {
            return back()->with('error', 'Only pending registrations can be approved.');
        }

        $registration->update([
            'status' => 'approved',
            'qr_sticker_id' => 'PSAU-' . strtoupper(Str::random(8)),
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        $user = $registration->user;
        $message = "Congratulations! Your sticker application for {$registration->vehicle->make} {$registration->vehicle->model} has been APPROVED! Please check your dashboard for scheduling instructions.";
        $user->notify(new \App\Notifications\RegistrationStatusUpdated($registration, 'approved', $message));

        return back()->with('success', 'Registration approved successfully. QR Sticker generated.');
    }

    /**
     * Reject a vehicle registration.
     */
    public function reject(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);

        if ($registration->status !== 'pending') {
            return back()->with('error', 'Only pending registrations can be rejected.');
        }

        // We can add validation for a rejection reason later if needed
        $registration->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $request->input('reason', 'Document details do not match or are invalid.'),
        ]);

        $user = $registration->user;
        $reason = $registration->rejection_reason;
        $message = "Your sticker application for {$registration->vehicle->make} {$registration->vehicle->model} was REJECTED. Reason: {$reason}";
        $user->notify(new \App\Notifications\RegistrationStatusUpdated($registration, 'rejected', $message));

        return back()->with('success', 'Registration rejected.');
    }
}
