<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\PickupSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AdminApprovedRegistrationController extends Controller
{
    private ?bool $hasQrPrintTrackingColumns = null;

    /**
     * Display a listing of approved registrations.
     */
    public function index()
    {
        $approvedRegistrations = Registration::with(['user', 'vehicle', 'pickupSchedule'])
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->orderBy('approved_at', 'desc')
            ->paginate(15);

        $autoSuggestions = [
            'day_after_tomorrow' => $this->nextBusinessSlot(
                Carbon::today()->addDays(2),
                8,
                0
            ),
            'next_week' => $this->nextBusinessSlot(
                Carbon::today()->addWeek(),
                8,
                0
            ),
        ];

        return view('admin.approved.index', compact('approvedRegistrations', 'autoSuggestions'));
    }

    /**
     * Generate and display the printable QR Sticker.
     */
    public function generateQr(Registration $registration)
    {
        if (strtolower($registration->status) !== 'approved' || empty($registration->qr_sticker_id)) {
            return redirect()->route('admin.approved.index')
                ->with('error', 'No QR sticker assigned or this registration is not yet approved.');
        }

        if ($this->supportsQrPrintTracking()) {
            $registration->increment('qr_print_count');
            $registration->update(['last_qr_printed_at' => now()]);
        }

        // URL that the main QR code will encode (public scan profile with sticker ID)
        $url = route('scan.show', $registration->qr_sticker_id);

        // Generate the main SVG QR code with High error correction (30% recovery) for lumpy/curved surfaces
        $qrCodeSvg = QrCode::size(260)->errorCorrection('H')->generate($url);

        return view('admin.approved.qr-print', compact('registration', 'qrCodeSvg'));

    }

    /**
     * Generate printable QR stickers for multiple registrations.
     */
    public function bulkPrintQr(Request $request)
    {
        $validated = $request->validate([
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['integer', 'distinct'],
        ]);

        $registrations = Registration::with(['user', 'vehicle'])
            ->whereIn('id', $validated['registration_ids'])
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->whereNotNull('qr_sticker_id')
            ->orderBy('id')
            ->get();

        if ($registrations->isEmpty()) {
            return redirect()->route('admin.approved.index')
                ->with('error', 'No valid approved registrations with QR sticker found from your selection.');
        }

        $qrCodeByRegistrationId = [];
        foreach ($registrations as $registration) {
            $url = route('scan.show', $registration->qr_sticker_id);
            $qrCodeByRegistrationId[$registration->id] = QrCode::size(180)
                ->errorCorrection('H')
                ->generate($url);
        }

        if ($this->supportsQrPrintTracking()) {
            foreach ($registrations as $registration) {
                $registration->increment('qr_print_count');
                $registration->update(['last_qr_printed_at' => now()]);
            }
        }

        return view('admin.approved.qr-print-bulk', [
            'registrations' => $registrations,
            'qrCodeByRegistrationId' => $qrCodeByRegistrationId,
        ]);
    }
    
    /**
     * Schedule a pick-up time for the user.
     */
    public function schedulePickup(Request $request, Registration $registration)
    {
        $request->validate([
            'auto_schedule_option' => 'nullable|in:day_after_tomorrow,next_week',
            'pickup_date'  => 'nullable|date|after_or_equal:today',
            'pickup_time'  => 'nullable|date_format:H:i',
            'location'     => 'required|string|max:255',
        ]);

        $autoOption = $request->input('auto_schedule_option');
        $manualDate = $request->input('pickup_date');
        $manualTime = $request->input('pickup_time');

        if (!$autoOption && (!$manualDate || !$manualTime)) {
            throw ValidationException::withMessages([
                'pickup_date' => 'Pick-up date and time are required.',
            ]);
        }

        if ($autoOption) {
            $baseDate = $autoOption === 'next_week'
                ? Carbon::today()->addWeek()
                : Carbon::today()->addDays(2);
            $scheduledAt = $this->nextBusinessSlot($baseDate, 8, 0);
        } else {
            $scheduledAt = Carbon::parse($manualDate . ' ' . $manualTime);
            $this->assertBusinessWindow($scheduledAt);
        }
        
        $schedule = PickupSchedule::updateOrCreate(
            ['registration_id' => $registration->id],
            [
                'pickup_date'  => $scheduledAt->toDateString(),
                'pickup_time'  => $scheduledAt->format('H:i'),
                'location'     => $request->location,
                'is_completed' => false,
            ]
        );
        
        if ($registration->user) {
            $registration->user->notify(new \App\Notifications\PickupScheduled($registration, $schedule));
        }
        
        return back()->with('success', 'Pick-up schedule saved successfully and user notified.');
    }

    private function nextBusinessSlot(Carbon $start, int $hour = 8, int $minute = 0): Carbon
    {
        $candidate = $start->copy()->setTime($hour, $minute);
        while (!$this->isBusinessDay($candidate)) {
            $candidate->addDay()->setTime($hour, $minute);
        }
        return $candidate;
    }

    private function assertBusinessWindow(Carbon $dateTime): void
    {
        if (!$this->isBusinessDay($dateTime)) {
            throw ValidationException::withMessages([
                'pickup_date' => 'Pick-up is only allowed Monday to Thursday.',
            ]);
        }

        $time = $dateTime->format('H:i');
        if ($time < '08:00' || $time > '16:00') {
            throw ValidationException::withMessages([
                'pickup_time' => 'Pick-up time must be between 8:00 AM and 4:00 PM.',
            ]);
        }
    }

    private function isBusinessDay(Carbon $date): bool
    {
        // ISO-8601: Monday=1 ... Sunday=7
        return $date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 4;
    }

    private function supportsQrPrintTracking(): bool
    {
        if ($this->hasQrPrintTrackingColumns !== null) {
            return $this->hasQrPrintTrackingColumns;
        }

        $this->hasQrPrintTrackingColumns = Schema::hasColumn('registrations', 'qr_print_count')
            && Schema::hasColumn('registrations', 'last_qr_printed_at');

        return $this->hasQrPrintTrackingColumns;
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
