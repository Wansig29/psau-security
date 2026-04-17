<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleChangeRequest;
use App\Models\Vehicle;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminVehicleChangeController extends Controller
{
    public function index()
    {
        $requests = VehicleChangeRequest::with(['user', 'oldVehicle', 'oldRegistration', 'reviewedBy'])
            ->latest()
            ->paginate(20);

        $pendingCount = VehicleChangeRequest::where('status', 'pending')->count();

        return view('admin.vehicle-changes.index', compact('requests', 'pendingCount'));
    }

    public function approve(Request $request, $id)
    {
        $changeRequest = VehicleChangeRequest::with(['user', 'oldVehicle', 'oldRegistration'])->findOrFail($id);

        if ($changeRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        DB::transaction(function () use ($changeRequest, $request) {
            // 1. Revoke old registration (mark as superseded)
            if ($changeRequest->oldRegistration) {
                $changeRequest->oldRegistration->update(['status' => 'superseded']);
            }

            // 2. Create new vehicle record
            $newVehicle = Vehicle::create([
                'user_id'      => $changeRequest->user_id,
                'plate_number' => $changeRequest->new_plate_number
                    ?? ('PENDING_' . strtoupper(\Illuminate\Support\Str::random(6))),
                'make'         => $changeRequest->new_make,
                'model'        => $changeRequest->new_model,
                'color'        => $changeRequest->new_color,
            ]);

            // 3. Create new registration for the new vehicle
            $currentYear = date('n') >= 8
                ? date('Y') . '-' . (date('Y') + 1)
                : (date('Y') - 1) . '-' . date('Y');

            $newRegistration = Registration::create([
                'user_id'     => $changeRequest->user_id,
                'vehicle_id'  => $newVehicle->id,
                'school_year' => $currentYear,
                'status'      => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            // 4. Save the submitted documents against the new registration
            $docPaths = $changeRequest->document_paths ?? [];
            $docBlobs = $changeRequest->image_data ?? [];
            $docTypes = ['vehicle_photo','or','cr','cor','license','school_id'];
            foreach ($docTypes as $type) {
                if (!empty($docPaths[$type])) {
                    \App\Models\RegistrationDocument::create([
                        'registration_id' => $newRegistration->id,
                        'document_type'   => $type,
                        'image_path'      => $docPaths[$type],
                        'image_data'      => !empty($docBlobs[$type]) ? base64_decode($docBlobs[$type]) : null,
                        'match_score'     => 0,
                    ]);
                }
            }

            // 5. Update the change request itself
            $changeRequest->update([
                'status'      => 'approved',
                'admin_notes' => $request->input('admin_notes'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // 6. Notify the user
            try {
                $changeRequest->user->notify(
                    new \App\Notifications\RegistrationApproved($newRegistration)
                );
            } catch (\Exception $e) {
                Log::warning('VehicleChange approval notification failed: ' . $e->getMessage());
            }
        });

        return back()->with('status', 'Vehicle change request approved. New registration created.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['admin_notes' => 'required|string|max:500']);

        $changeRequest = VehicleChangeRequest::findOrFail($id);

        if ($changeRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $changeRequest->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Vehicle change request rejected.');
    }

    public function showImage($id, $type)
    {
        $changeRequest = VehicleChangeRequest::findOrFail($id);
        $blobs = $changeRequest->image_data ?? [];

        if (empty($blobs[$type])) {
            abort(404, 'Image not found.');
        }

        $binary = base64_decode($blobs[$type]);
        return response($binary, 200)
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'private, max-age=3600');
    }
}
