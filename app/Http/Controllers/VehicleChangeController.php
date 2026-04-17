<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\VehicleChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleChangeController extends Controller
{
    public function create()
    {
        $user = auth()->user();

        // Must have an approved registration to request a change
        $activeRegistration = Registration::with('vehicle')
            ->where('user_id', $user->id)
            ->whereRaw("LOWER(status) = 'approved'")
            ->latest()->first();

        if (!$activeRegistration) {
            return redirect()->route('user.dashboard')
                ->with('error', 'You must have an approved vehicle registration before requesting a change.');
        }

        // Prevent duplicate pending requests
        $pending = VehicleChangeRequest::where('user_id', $user->id)
            ->where('status', 'pending')->exists();

        if ($pending) {
            return redirect()->route('user.dashboard')
                ->with('error', 'You already have a pending vehicle change request. Please wait for admin review.');
        }

        return view('user.vehicle-change.create', compact('activeRegistration'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'new_make'          => 'required|string|max:255',
            'new_model'         => 'required|string|max:255',
            'new_color'         => 'required|string|max:255',
            'reason'            => 'required|string|max:1000',
            'doc_vehicle_photo' => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:10240',
            'doc_or'            => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:10240',
            'doc_cr'            => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:10240',
            'doc_cor'           => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:10240',
            'doc_license'       => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:10240',
            'doc_school_id'     => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:10240',
        ]);

        $user = auth()->user();

        $activeRegistration = Registration::with('vehicle')
            ->where('user_id', $user->id)
            ->whereRaw("LOWER(status) = 'approved'")
            ->latest()->first();

        if (!$activeRegistration) {
            return back()->with('error', 'No active approved registration found.');
        }

        // Helper: compress + store document
        $storeDoc = function ($file, $folder) {
            $path     = $file->store("vehicle-changes/{$folder}", 'public');
            $fullPath = storage_path('app/public/' . $path);
            try {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $img     = $manager->read($fullPath);
                if ($img->width() > 1200) { $img->scale(width: 1200); }
                $img->toJpeg(70)->save($fullPath);
            } catch (\Exception $e) {
                Log::error('VehicleChange image compression failed: ' . $e->getMessage());
            }
            $data = file_exists($fullPath) ? base64_encode(file_get_contents($fullPath)) : null;
            return ['path' => $path, 'data' => $data];
        };

        $fileDefs = [
            'vehicle_photo' => $request->file('doc_vehicle_photo'),
            'or'            => $request->file('doc_or'),
            'cr'            => $request->file('doc_cr'),
            'cor'           => $request->file('doc_cor'),
            'license'       => $request->file('doc_license'),
            'school_id'     => $request->file('doc_school_id'),
        ];

        $paths = [];
        $blobs = [];
        foreach ($fileDefs as $key => $file) {
            $result = $storeDoc($file, $key);
            $paths[$key] = $result['path'];
            $blobs[$key] = $result['data'];
        }

        // OCR: try to extract plate from OR/CR
        $plateNumber = null;
        try {
            DB::reconnect();
            $orPath = storage_path('app/public/' . $paths['or']);
            $ocrText = (new \thiagoalessio\TesseractOCR\TesseractOCR($orPath))->run();
            if (preg_match('/([A-Z]{2,3}[\s-]?[0-9]{3,4}|[0-9]{3,4}[\s-]?[A-Z]{2,3})/', strtoupper($ocrText), $m)) {
                $plateNumber = str_replace([' ', '-'], '', $m[0]);
            }
        } catch (\Exception $e) {
            Log::warning('VehicleChange OCR failed: ' . $e->getMessage());
        }

        DB::reconnect();

        VehicleChangeRequest::create([
            'user_id'             => $user->id,
            'old_vehicle_id'      => $activeRegistration->vehicle_id,
            'old_registration_id' => $activeRegistration->id,
            'new_make'            => $request->new_make,
            'new_model'           => $request->new_model,
            'new_color'           => $request->new_color,
            'new_plate_number'    => $plateNumber,
            'reason'              => $request->reason,
            'document_paths'      => $paths,
            'image_data'          => $blobs,
            'status'              => 'pending',
        ]);

        return redirect()->route('user.dashboard')
            ->with('status', 'Vehicle change request submitted! An admin will review it shortly.');
    }
}
