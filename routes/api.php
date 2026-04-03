<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — PSAU Parking System (Laravel Sanctum)
|--------------------------------------------------------------------------
| All routes require Bearer token from Sanctum unless marked [public].
*/

// ─── [PUBLIC] Auth ────────────────────────────────────────────────────────────

Route::post('/login', function (Request $request) {
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    if (!\Illuminate\Support\Facades\Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }

    $user  = \App\Models\User::where('email', $request->email)->firstOrFail();
    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => [
            'id'                   => $user->id,
            'name'                 => $user->name,
            'email'                => $user->email,
            'role'                 => $user->role,
            'contact_number'       => $user->contact_number,
            'profile_photo_path'   => $user->profile_photo_path
                ? \Illuminate\Support\Facades\Storage::url($user->profile_photo_path)
                : null,
        ],
    ]);
});

Route::post('/register', function (Request $request) {
    $request->validate([
        'name'                  => 'required|string|max:255',
        'email'                 => 'required|email|unique:users',
        'password'              => 'required|string|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        'role'     => 'vehicle_user',
    ]);

    return response()->json(['message' => 'Registration successful. Please log in.'], 201);
});

// ─── [PUBLIC] QR Scan ─────────────────────────────────────────────────────────

Route::get('/scan/{qr_sticker_id}', function ($qr_sticker_id) {
    $registration = \App\Models\Registration::with(['vehicle', 'user'])
        ->where('qr_sticker_id', $qr_sticker_id)
        ->first();

    if (!$registration) {
        return response()->json(['message' => 'QR sticker not found.'], 404);
    }

    $vehicle = $registration->vehicle;
    $owner   = $registration->user;

    return response()->json([
        'registration_status' => $registration->status,
        'school_year'         => $registration->school_year,
        'qr_sticker_id'       => $registration->qr_sticker_id,
        'vehicle' => [
            'make'         => $vehicle->make,
            'model'        => $vehicle->model,
            'color'        => $vehicle->color,
            'plate_number' => $vehicle->plate_number,
        ],
        'owner' => [
            'name'           => $owner->name,
            'contact_number' => $owner->contact_number,
        ],
    ]);
});

// ─── Authenticated Routes ─────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Auth: logout & profile
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    });

    Route::get('/me', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'role'               => $user->role,
            'contact_number'     => $user->contact_number,
            'profile_photo_path' => $user->profile_photo_path
                ? \Illuminate\Support\Facades\Storage::url($user->profile_photo_path)
                : null,
            'current_lat'           => $user->current_lat,
            'current_lng'           => $user->current_lng,
            'last_location_update'  => $user->last_location_update,
        ]);
    });

    Route::patch('/profile/update', function (Request $request) {
        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
        ]);
        $request->user()->update($request->only('name', 'email'));
        return response()->json(['message' => 'Profile updated.']);
    });

    Route::delete('/profile/delete', function (Request $request) {
        $request->user()->delete();
        return response()->json(['message' => 'Account deleted.']);
    });

    // Notifications
    Route::get('/notifications', function (Request $request) {
        return response()->json(
            $request->user()->notifications()->latest()->take(50)->get()
        );
    });

    Route::post('/notifications/read/{id}', function (Request $request, $id) {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['message' => 'Marked as read.']);
    });

    Route::post('/notifications/read-all', function (Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    });

    // ─── Vehicle User Routes ──────────────────────────────────────────────────

    Route::prefix('user')->middleware('role:vehicle_user')->group(function () {

        Route::get('/dashboard', function (Request $request) {
            $user         = $request->user();
            $registration = \App\Models\Registration::with(['vehicle', 'pickupSchedule'])
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            $activeSanctions = [];
            if ($registration && $registration->vehicle) {
                $activeSanctions = \App\Models\Sanction::where('vehicle_id', $registration->vehicle->id)
                    ->where('is_active', true)
                    ->get();
            }

            return response()->json([
                'registration'    => $registration,
                'active_sanctions'=> $activeSanctions,
            ]);
        });

        Route::post('/registration/submit', function (Request $request) {
            $request->validate([
                'contact_number' => ['nullable', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'make'           => 'required|string|max:255',
                'model'          => 'required|string|max:255',
                'color'          => 'required|string|max:255',
                'doc_or'         => 'required|file|mimes:jpeg,png,jpg|max:5120',
                'doc_cr'         => 'required|file|mimes:jpeg,png,jpg|max:5120',
                'doc_cor'        => 'required|file|mimes:jpeg,png,jpg|max:5120',
                'doc_license'    => 'required|file|mimes:jpeg,png,jpg|max:5120',
                'doc_school_id'  => 'required|file|mimes:jpeg,png,jpg|max:5120',
            ]);

            $user = $request->user();

            if ($request->filled('contact_number')) {
                $user->update(['contact_number' => $request->contact_number]);
            }

            $storeDoc = fn($file, $folder) => $file->store("registrations/{$folder}", 'public');

            $vehicle = \App\Models\Vehicle::create([
                'user_id'      => $user->id,
                'plate_number' => 'PENDING_' . strtoupper(\Illuminate\Support\Str::random(8)),
                'make'         => $request->make,
                'model'        => $request->model,
                'color'        => $request->color,
            ]);

            $currentYear  = date('Y') . '-' . (date('Y') + 1);
            $registration = \App\Models\Registration::create([
                'user_id'    => $user->id,
                'vehicle_id' => $vehicle->id,
                'school_year'=> $currentYear,
                'status'     => 'pending',
            ]);

            $docs = [
                'or'        => $storeDoc($request->file('doc_or'),        'or'),
                'cr'        => $storeDoc($request->file('doc_cr'),        'cr'),
                'cor'       => $storeDoc($request->file('doc_cor'),       'cor'),
                'license'   => $storeDoc($request->file('doc_license'),   'license'),
                'school_id' => $storeDoc($request->file('doc_school_id'), 'school_id'),
            ];

            foreach ($docs as $type => $path) {
                \App\Models\RegistrationDocument::create([
                    'registration_id' => $registration->id,
                    'document_type'   => $type,
                    'image_path'      => $path,
                    'match_score'     => 0,
                ]);
            }

            return response()->json(['message' => 'Registration submitted successfully.', 'registration_id' => $registration->id], 201);
        });

        Route::post('/profile/photo', function (Request $request) {
            $request->validate(['photo' => 'required|image|mimes:jpeg,png,jpg|max:5120']);
            $path = $request->file('photo')->store('profile-photos', 'public');
            $request->user()->update(['profile_photo_path' => $path]);
            return response()->json([
                'message'   => 'Photo updated.',
                'photo_url' => \Illuminate\Support\Facades\Storage::url($path),
            ]);
        });

        Route::delete('/profile/photo/remove', function (Request $request) {
            $request->user()->update(['profile_photo_path' => null]);
            return response()->json(['message' => 'Photo removed.']);
        });

        Route::post('/location/broadcast', function (Request $request) {
            $request->validate([
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);
            $request->user()->update([
                'current_lat'          => $request->lat,
                'current_lng'          => $request->lng,
                'last_location_update' => now(),
            ]);
            return response()->json(['message' => 'Location updated.']);
        });

        Route::post('/contact/update', function (Request $request) {
            $request->validate([
                'contact_number' => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            ]);
            $request->user()->update(['contact_number' => $request->contact_number]);
            return response()->json(['message' => 'Contact number updated.']);
        });
    });

    // ─── Security Routes ──────────────────────────────────────────────────────

    Route::prefix('security')->middleware('role:security')->group(function () {

        Route::get('/dashboard', function (Request $request) {
            $recentViolations = \App\Models\Violation::with(['vehicle.user'])
                ->where('logged_by', $request->user()->id)
                ->latest()
                ->take(10)
                ->get();

            return response()->json(['recent_violations' => $recentViolations]);
        });

        Route::get('/search', function (Request $request) {
            $query = $request->input('query');
            if (!$query) {
                return response()->json(['message' => 'Query is required.'], 422);
            }

            $queryUpper = strtoupper($query);

            $vehicle = \App\Models\Vehicle::with([
                'registrations' => fn($q) => $q->orderByRaw("CASE WHEN LOWER(status)='approved' THEN 0 WHEN LOWER(status)='pending' THEN 1 ELSE 2 END")->limit(1),
                'user',
            ])
            ->whereRaw('UPPER(plate_number) LIKE ?', ["%{$queryUpper}%"])
            ->orWhereHas('registrations', fn($q) => $q->whereRaw('UPPER(qr_sticker_id) LIKE ?', ["%{$queryUpper}%"]))
            ->first();

            if (!$vehicle) {
                return response()->json(['message' => 'No vehicle found.'], 404);
            }

            $registration = $vehicle->registrations->first();

            return response()->json([
                'vehicle' => [
                    'id'           => $vehicle->id,
                    'plate_number' => $vehicle->plate_number,
                    'make'         => $vehicle->make,
                    'model'        => $vehicle->model,
                    'color'        => $vehicle->color,
                ],
                'owner' => $vehicle->user ? [
                    'id'             => $vehicle->user->id,
                    'name'           => $vehicle->user->name,
                    'contact_number' => $vehicle->user->contact_number,
                ] : null,
                'registration' => $registration ? [
                    'id'            => $registration->id,
                    'status'        => $registration->status,
                    'qr_sticker_id' => $registration->qr_sticker_id,
                ] : null,
            ]);
        });

        Route::get('/location', function () {
            $users = \App\Models\User::where('role', 'vehicle_user')
                ->whereNotNull('current_lat')
                ->whereNotNull('current_lng')
                ->get()
                ->map(fn($u) => [
                    'id'               => $u->id,
                    'name'             => $u->name,
                    'contact_number'   => $u->contact_number,
                    'lat'              => $u->current_lat,
                    'lng'              => $u->current_lng,
                    'last_seen'        => $u->last_location_update?->diffForHumans(),
                    'last_seen_time'   => $u->last_location_update?->format('M d, Y g:i A'),
                    'is_online'        => $u->last_location_update && $u->last_location_update->diffInMinutes(now()) <= 5,
                ]);

            return response()->json(['users' => $users]);
        });

        Route::post('/violations/issue', function (Request $request) {
            $request->validate([
                'vehicle_id'      => 'required|exists:vehicles,id',
                'registration_id' => 'nullable|exists:registrations,id',
                'violation_type'  => 'required|string',
                'location_notes'  => 'required|string',
                'photo_image'     => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
                'gps_lat'         => 'nullable|numeric',
                'gps_lng'         => 'nullable|numeric',
            ]);

            $photoPath = null;
            if ($request->hasFile('photo_image')) {
                $photoPath = $request->file('photo_image')->store('violations', 'public');
            }

            $currentYear = date('Y') . '-' . (date('Y') + 1);

            $violation = \App\Models\Violation::create([
                'vehicle_id'       => $request->vehicle_id,
                'registration_id'  => $request->registration_id,
                'school_year'      => $currentYear,
                'violation_type'   => $request->violation_type,
                'location_notes'   => $request->location_notes,
                'photo_path'       => $photoPath,
                'gps_lat'          => $request->gps_lat,
                'gps_lng'          => $request->gps_lng,
                'logged_by'        => $request->user()->id,
                'sanction_applied' => false,
            ]);

            // Auto-Sanction Logic (mirrored from web ViolationController)
            $today = \Carbon\Carbon::today();
            if ($request->violation_type === 'unregistered_no_license') {
                $endDate     = $today->copy()->addDays(7);
                $description = 'Special offense: Unregistered vehicle + No license. 7-day ban.';
                $sanctionType = 'Suspended';
            } else {
                $priorCount    = \App\Models\Violation::where('vehicle_id', $request->vehicle_id)->where('id', '!=', $violation->id)->count();
                $offenseNumber = $priorCount + 1;
                if ($offenseNumber === 1) {
                    $endDate = $today->copy()->addDays(3); $description = '1st offense: 3-day ban.'; $sanctionType = 'Suspended';
                } elseif ($offenseNumber === 2) {
                    $endDate = $today->copy()->addDays(5); $description = '2nd offense: 5-day ban.'; $sanctionType = 'Suspended';
                } else {
                    $endDate = \Carbon\Carbon::create((int)date('Y') + 1, 6, 30); $description = "Offense #{$offenseNumber}: Semester ban."; $sanctionType = 'Revoked';
                }
            }

            \App\Models\Sanction::create([
                'vehicle_id'   => $request->vehicle_id,
                'violation_id' => $violation->id,
                'sanction_type'=> $sanctionType,
                'start_date'   => $today,
                'end_date'     => $endDate,
                'is_active'    => true,
                'source'       => 'auto',
                'description'  => $description,
            ]);

            $violation->update(['sanction_applied' => true]);

            if ($violation->vehicle?->user) {
                $violation->vehicle->user->notify(
                    new \App\Notifications\ViolationLogged($violation, $description)
                );
            }

            return response()->json(['message' => 'Violation logged and sanction applied.', 'violation_id' => $violation->id], 201);
        });
    });

    // ─── Admin Routes ─────────────────────────────────────────────────────────

    Route::prefix('admin')->middleware('role:admin')->group(function () {

        Route::get('/dashboard', function () {
            return response()->json([
                'total_users'          => \App\Models\User::count(),
                'pending_registrations'=> \App\Models\Registration::whereRaw("LOWER(status) = 'pending'")->count(),
                'active_violations'    => \App\Models\Violation::count(),
                'active_sanctions'     => \App\Models\Sanction::where('is_active', true)->count(),
            ]);
        });

        // User management
        Route::get('/users', function () {
            return response()->json(\App\Models\User::select('id','name','email','role','created_at')->get());
        });

        Route::post('/users/create', function (Request $request) {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users',
                'password' => 'required|string|min:8',
                'role'     => 'required|in:admin,security,vehicle_user',
            ]);
            $user = \App\Models\User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role'     => $request->role,
            ]);
            return response()->json(['message' => 'User created.', 'user' => $user], 201);
        });

        Route::delete('/users/delete/{id}', function ($id) {
            $user = \App\Models\User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'User deleted.']);
        });

        // Registration management
        Route::get('/registrations/pending', function () {
            return response()->json(
                \App\Models\Registration::with(['user', 'vehicle', 'documents'])
                    ->whereRaw("LOWER(status) = 'pending'")->latest()->get()
            );
        });

        Route::post('/registrations/approve/{id}', function (Request $request, $id) {
            $registration = \App\Models\Registration::with('vehicle')->findOrFail($id);
            $qrId = strtoupper(\Illuminate\Support\Str::random(10));
            $registration->update([
                'status'        => 'approved',
                'qr_sticker_id' => $qrId,
                'approved_at'   => now(),
            ]);
            if ($registration->user) {
                $registration->user->notify(new \App\Notifications\RegistrationApproved($registration));
            }
            return response()->json(['message' => 'Registration approved.', 'qr_sticker_id' => $qrId]);
        });

        Route::post('/registrations/reject/{id}', function (Request $request, $id) {
            $request->validate(['reason' => 'required|string']);
            $registration = \App\Models\Registration::findOrFail($id);
            $registration->update([
                'status'           => 'rejected',
                'rejection_reason' => $request->reason,
            ]);
            return response()->json(['message' => 'Registration rejected.']);
        });

        // Vehicle / QR / Pickup
        Route::get('/vehicles', function () {
            $registrations = \App\Models\Registration::with(['vehicle', 'user', 'pickupSchedule'])
                ->whereRaw("LOWER(status) = 'approved'")
                ->latest()
                ->get();

            return response()->json(
                $registrations->map(function ($r) {
                    $pickup = $r->pickupSchedule;
                    return [
                        'id'              => $r->id,
                        'qr_sticker_id'  => $r->qr_sticker_id,
                        'status'         => $r->status,
                        'approved_at'    => $r->approved_at,
                        'vehicle'        => $r->vehicle,
                        'user'           => $r->user,
                        'pickup_schedule' => $pickup ? [
                            'pickup_date'      => $pickup->pickup_date?->format('Y-m-d'),
                            'pickup_time'      => $pickup->pickup_time,
                            'pickup_location'  => $pickup->location,
                            'is_claimed'       => (bool) $pickup->is_completed,
                            'completed_at'     => $pickup->completed_at,
                        ] : null,
                    ];
                })
            );
        });

        Route::get('/vehicles/generate-qr/{id}', function ($id) {
            $registration = \App\Models\Registration::findOrFail($id);
            return response()->json(['qr_sticker_id' => $registration->qr_sticker_id]);
        });

        Route::post('/vehicles/schedule-pickup/{id}', function (Request $request, $id) {
            $request->validate([
                'pickup_date'     => 'required|date',
                'pickup_location' => 'required|string|max:255',
            ]);
            $registration = \App\Models\Registration::findOrFail($id);

            // pickup_time is required by schema; default to 08:00 when not provided by the app.
            $registration->pickupSchedule()->updateOrCreate(
                ['registration_id' => $registration->id],
                [
                    'pickup_date'  => $request->pickup_date,
                    'pickup_time'  => '08:00',
                    'location'     => $request->pickup_location,
                    'is_completed' => false,
                    'completed_at' => null,
                    'completed_by' => null,
                ]
            );

            return response()->json(['message' => 'Pickup scheduled.']);
        });

        Route::post('/vehicles/mark-claimed/{id}', function ($id) {
            $registration = \App\Models\Registration::with('pickupSchedule')->findOrFail($id);
            $schedule = $registration->pickupSchedule;

            if (!$schedule) {
                return response()->json(['message' => 'No schedule found.'], 404);
            }

            $schedule->update([
                'is_completed' => true,
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);

            return response()->json(['message' => 'Marked as claimed.']);
        });

        // Sanctions
        Route::get('/sanctions', function () {
            return response()->json([
                'active'   => \App\Models\Sanction::with(['vehicle.user', 'violation'])->where('is_active', true)->latest()->get(),
                'resolved' => \App\Models\Sanction::with(['vehicle.user', 'violation'])->where('is_active', false)->latest()->get(),
            ]);
        });

        Route::post('/sanctions/add', function (Request $request) {
            $request->validate([
                'vehicle_id'    => 'required|exists:vehicles,id',
                'sanction_type' => 'required|string',
                'start_date'    => 'required|date',
                'end_date'      => 'required|date|after:start_date',
                'description'   => 'nullable|string',
            ]);
            $sanction = \App\Models\Sanction::create([
                'vehicle_id'    => $request->vehicle_id,
                'sanction_type' => $request->sanction_type,
                'start_date'    => $request->start_date,
                'end_date'      => $request->end_date,
                'is_active'     => true,
                'source'        => 'manual',
                'description'   => $request->description,
            ]);
            return response()->json(['message' => 'Sanction added.', 'sanction' => $sanction], 201);
        });

        Route::post('/sanctions/resolve/{id}', function ($id) {
            $sanction = \App\Models\Sanction::findOrFail($id);
            $sanction->update(['is_active' => false]);
            return response()->json(['message' => 'Sanction resolved.']);
        });
    });
});
