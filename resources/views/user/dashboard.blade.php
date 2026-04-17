@extends('layouts.adminlte')

@section('title', 'My Dashboard')

@section('content')

<style>
    @media (max-width: 576px) {
        .profile-hero .avatar-row { flex-direction: column !important; align-items: center !important; }
        .profile-hero .button-wrap { width: 100%; text-align: center; margin-top: 8px; }
        .profile-hero .button-wrap a { width: 100%; display: block; }
        .profile-hero .profile-user-info { text-align: center; }
        .profile-hero .profile-user-info .badges { justify-content: center; }
        .profile-hero-tabs .nav-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
            padding-bottom: 2px;
        }
        .profile-hero-tabs .nav-tabs::-webkit-scrollbar { display: none; }
    }
    @media (max-width: 420px) {
        .profile-hero > div:last-of-type { padding: 0 12px 16px !important; }
        .profile-hero .avatar-row { margin-top: -40px !important; gap: 8px !important; }
        .profile-hero .profile-user-info h2 { font-size: 1.15rem !important; }
        .profile-hero .profile-user-info .badges .badge { font-size: .7rem !important; padding: 3px 8px !important; }
        .profile-hero-tabs .nav-link { padding: 10px 14px !important; font-size: .82rem !important; }
        .profile-hero-tabs .nav-link i { display: none; }
    }

    /* iOS Safari-specific size tuning (SE, mini, and standard iPhones) */
    @supports (-webkit-touch-callout: none) {
        @media (max-width: 430px) {
            .profile-hero > div:first-of-type { height: 104px !important; }
            .profile-hero .avatar-row { margin-top: -38px !important; }
            .profile-hero .avatar-row > div:first-child > div {
                width: 84px !important;
                height: 84px !important;
            }
            .profile-hero .button-wrap a {
                font-size: .9rem !important;
                padding: 8px 14px !important;
            }
            .profile-hero .profile-user-info h2 { font-size: 1.1rem !important; }
            .profile-hero .profile-user-info .badges .badge {
                font-size: .68rem !important;
                padding: 3px 7px !important;
            }
        }

        @media (max-width: 375px) {
            .profile-hero > div:last-of-type { padding: 0 10px 14px !important; }
            .profile-hero .button-wrap a {
                font-size: .85rem !important;
                padding: 8px 12px !important;
            }
            .profile-hero-tabs .nav-link {
                padding: 9px 12px !important;
                font-size: .78rem !important;
            }
        }
    }
</style>

{{-- ── Alerts ─────────────────────────────────────────────────────────── --}}
@if(session('status'))
<div class="alert alert-success alert-dismissible fade show shadow-sm">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <ul class="mb-0 pl-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- PROFILE HERO CARD                                       --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-lg mb-4 profile-hero" style="border:none;overflow:visible">

    {{-- Maroon banner (the avatar hangs below it, not over the text) --}}
    <div style="height:120px;background:linear-gradient(135deg,#7b1113 0%,#b22222 60%,#c0392b 100%);
                position:relative;border-radius:4px 4px 0 0;overflow:hidden">
        {{-- Decorative circles --}}
        <div style="position:absolute;top:-20px;right:-20px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.07)"></div>
        <div style="position:absolute;bottom:-30px;left:40%;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.05)"></div>
    </div>

    {{-- White body area --}}
    <div style="background:#fff;border:1px solid #e3e6f0;border-top:none;border-radius:0 0 4px 4px;padding:0 24px 20px">

        {{-- Avatar row: avatar floats over the top edge of this white area --}}
        <div class="avatar-row" style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:-46px">

            {{-- Avatar --}}
            <div style="position:relative;flex-shrink:0">
                <div style="width:92px;height:92px;border-radius:50%;border:4px solid #fff;overflow:hidden;
                            background:#e9ecef;box-shadow:0 4px 15px rgba(0,0,0,0.2)">
                    @if(auth()->user()->profile_photo_path)
                        <img id="profileThumb"
                             src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                             style="width:100%;height:100%;object-fit:cover"
                             alt="Profile Photo">
                    @else
                        <div id="profileThumb" style="width:100%;height:100%;display:flex;align-items:center;
                                    justify-content:center;background:linear-gradient(135deg,#7b1113,#b22222)">
                            <span style="font-size:2rem;font-weight:900;color:#fff;text-transform:uppercase">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>
                {{-- Camera icon --}}
                <label for="photoFileInput" title="Change profile photo"
                       style="position:absolute;bottom:2px;right:2px;width:26px;height:26px;border-radius:50%;
                              background:#7b1113;color:#fff;display:flex;align-items:center;justify-content:center;
                              cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.3);border:2px solid #fff;z-index:5">
                    <i class="fas fa-camera" style="font-size:9px"></i>
                </label>
                {{-- Hidden upload form --}}
                <form id="photoUploadForm" method="POST" action="{{ route('user.profile.photo.upload') }}"
                      enctype="multipart/form-data" style="display:none">
                    @csrf
                    <input type="file" id="photoFileInput" name="profile_photo"
                           accept="image/jpeg,image/png,image/jpg" onchange="previewAndSubmit(this)">
                </form>
            </div>

            {{-- Register or Change Vehicle button --}}
            <div class="button-wrap" style="padding-bottom:4px">
                @php
                    $latestReg = $registrations->first();
                    $latestStatus = $latestReg ? strtolower((string) $latestReg->status) : null;
                    $hasPendingChange = \App\Models\VehicleChangeRequest::where('user_id', auth()->id())->where('status','pending')->exists();
                @endphp
                @if($latestStatus === 'approved' && !$hasPendingChange)
                    <a href="{{ route('user.vehicle-change.create') }}"
                       class="btn font-weight-bold"
                       style="background:#1d4ed8;color:#fff;border-radius:8px;padding:9px 20px;
                              box-shadow:0 3px 10px rgba(29,78,216,0.3);white-space:nowrap;font-size:.95rem">
                        <i class="fas fa-exchange-alt mr-2"></i>Change Vehicle
                    </a>
                @elseif($hasPendingChange)
                    <span class="btn font-weight-bold"
                          style="background:#9ca3af;color:#fff;border-radius:8px;padding:9px 20px;
                                 cursor:not-allowed;white-space:nowrap;font-size:.95rem">
                        <i class="fas fa-clock mr-2"></i>Change Pending…
                    </span>
                @elseif(!$latestStatus || $latestStatus === 'rejected')
                    <a href="{{ route('user.registration.create') }}"
                       class="btn font-weight-bold"
                       style="background:#7b1113;color:#fff;border-radius:8px;padding:9px 20px;
                              box-shadow:0 3px 10px rgba(123,17,19,0.3);white-space:nowrap;font-size:.95rem">
                        <i class="fas fa-plus mr-2"></i>Register Vehicle
                    </a>
                @else
                    <span class="btn font-weight-bold"
                          style="background:#9ca3af;color:#fff;border-radius:8px;padding:9px 20px;
                                 cursor:not-allowed;white-space:nowrap;font-size:.95rem">
                        <i class="fas fa-clock mr-2"></i>Under Review…
                    </span>
                @endif
            </div>
        </div>

        {{-- Name / email / badges — these appear BELOW the avatar, no overlap --}}
        <div class="profile-user-info" style="padding-top:12px">
            <h2 style="font-size:1.45rem;font-weight:900;color:#1a1a2e;margin-bottom:2px;line-height:1.2">
                {{ $user->name }}
            </h2>
            <div style="color:#666;font-size:.87rem;margin-bottom:10px;word-break:break-word;overflow-wrap:anywhere">
                <i class="fas fa-envelope mr-1"></i>{{ $user->email }}
            </div>

            <div class="badges d-flex flex-wrap" style="gap:6px;margin-bottom:12px;max-width:100%">
                <span class="badge badge-pill"
                      style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;font-size:.76rem;padding:4px 10px">
                    <i class="fas fa-shield-alt mr-1"></i>Verified User
                </span>
                <span class="badge badge-pill"
                      style="background:#fff3cd;color:#856404;border:1px solid #ffeeba;font-size:.76rem;padding:4px 10px">
                    <i class="fas fa-car mr-1"></i>{{ $approvedVehiclesCount }} Approved Vehicle(s)
                </span>
                @if($violations->count() > 0)
                <span class="badge badge-pill badge-danger" style="font-size:.76rem;padding:4px 10px">
                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ $violations->count() }} Violation(s)
                </span>
                @endif
            </div>

            @if(auth()->user()->profile_photo_path)
                <form method="POST" action="{{ route('user.profile.photo.remove') }}" class="d-inline"
                      onsubmit="return confirm('Remove your profile photo?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-link btn-sm text-danger p-0">
                        <i class="fas fa-trash-alt mr-1"></i>Remove Photo
                    </button>
                </form>
            @endif
        </div>

    </div>

    {{-- Tabs row --}}
    <div class="profile-hero-tabs" style="background:#fff;border:1px solid #e3e6f0;border-top:1px solid #dee2e6;border-radius:0 0 4px 4px;margin-top:4px">
        <ul class="nav nav-tabs border-0" id="userTabs">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold px-4 py-3" data-toggle="tab" href="#tab-vehicles"
                   style="color:#7b1113;border-bottom:3px solid #7b1113;border-top:none;border-left:none;border-right:none">
                    <i class="fas fa-car mr-2"></i>My Vehicles
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-4 py-3 text-secondary" data-toggle="tab" href="#tab-registrations"
                   style="border:none">
                    <i class="fas fa-file-alt mr-2"></i>Applications
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-4 py-3 text-secondary" data-toggle="tab" href="#tab-violations"
                   style="border:none">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Violations
                    @if($violations->count() > 0)
                        <span class="badge badge-danger badge-pill ml-1">{{ $violations->count() }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TAB CONTENT                                              --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="tab-content">

    {{-- ── TAB 1: MY VEHICLES ──────────────────────────────── --}}
    <div class="tab-pane fade show active" id="tab-vehicles">
        @if($vehicles->isEmpty())
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-car fa-4x mb-3 text-muted"></i>
                    <h5 class="text-muted">No vehicles registered yet</h5>
                    <a href="{{ route('user.registration.create') }}" class="btn btn-danger mt-2">
                        <i class="fas fa-plus mr-2"></i>Register Your First Vehicle
                    </a>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($vehicles as $veh)
                <div class="col-sm-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100"
                         style="border-radius:12px;overflow:hidden;border:1px solid #e3e6f0;transition:transform .2s,box-shadow .2s;position:relative;"
                         onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,0.12)'"
                         onmouseout="this.style.transform='';this.style.boxShadow=''">
                         
                        <form action="{{ route('user.vehicle.destroy', $veh) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to remove this vehicle? All related data, including pending applications, will be removed.')"
                              style="position:absolute;top:8px;right:8px;z-index:10;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger rounded-circle shadow-sm" style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;" title="Remove Vehicle">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        {{-- Vehicle photo from registration documents --}}
                        @php
                            $vehPhotoPath = null;
                            $latestReg = $veh->registrations->first();
                            if ($latestReg) {
                                $vehDoc = $latestReg->documents->firstWhere('document_type', 'vehicle_photo');
                                $vehPhotoPath = $vehDoc ? $vehDoc->image_path : null;
                            }
                        @endphp
                        <div style="height:150px;overflow:hidden;background:#f1f3f9;display:flex;align-items:center;justify-content:center">
                            @if($vehPhotoPath)
                                <img src="{{ asset('storage/' . $vehPhotoPath) }}"
                                     style="width:100%;height:100%;object-fit:cover"
                                     alt="{{ $veh->plate_number }}"
                                     onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'text-center text-muted\'><i class=\'fas fa-car fa-3x mb-1\'></i><br><small>Photo unavailable</small></div>'">
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-car fa-3x mb-1"></i><br>
                                    <small>No Photo</small>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-3">
                            <h5 class="font-weight-bold mb-1">{{ $veh->make }} {{ $veh->model }}</h5>
                            <p class="text-muted small mb-2">
                                <span style="display:inline-block;width:12px;height:12px;border-radius:50%;
                                            background:{{ strtolower($veh->color) }};border:1px solid #ccc;
                                            vertical-align:middle;margin-right:4px"></span>
                                {{ $veh->color }}
                            </p>
                            <div class="text-center py-2 rounded" style="background:#f8f9fa;border:2px dashed #dee2e6">
                                <span style="font-family:monospace;font-size:1.4rem;font-weight:900;letter-spacing:4px;color:#1a1a2e">
                                    {{ $veh->plate_number }}
                                </span>
                            </div>
                        </div>
                        @php $reg = $veh->registrations->first(); @endphp
                        <div class="card-footer p-2 text-center" style="font-size:.8rem">
                            @if($reg && strtolower((string) $reg->status) === 'approved')
                                <span class="badge badge-success py-2 px-3"><i class="fas fa-check-circle mr-1"></i>Approved – {{ $reg->school_year }}</span>
                            @elseif($reg && strtolower((string) $reg->status) === 'pending')
                                <span class="badge badge-warning text-dark py-2 px-3"><i class="fas fa-clock mr-1"></i>Pending Review</span>
                            @elseif($reg && strtolower((string) $reg->status) === 'rejected')
                                <span class="badge badge-danger py-2 px-3"><i class="fas fa-times mr-1"></i>Rejected</span>
                            @else
                                <span class="badge badge-secondary py-2 px-3"><i class="fas fa-minus-circle mr-1"></i>No Application</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── TAB 2: REGISTRATION APPLICATIONS ───────────────── --}}
    <div class="tab-pane fade" id="tab-registrations">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 font-weight-bold"><i class="fas fa-file-alt mr-2 text-primary"></i>My Sticker Applications</h5>
            </div>
            <div class="card-body p-0">
                @if($registrations->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-3x mb-2"></i>
                        <p>No registration applications yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Plate No.</th>
                                    <th>School Year</th>
                                    <th>Documents</th>
                                    <th>Status</th>
                                    <th>QR Sticker ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registrations as $reg)
                                <tr>
                                    <td class="font-weight-bold">{{ $reg->vehicle->make }} {{ $reg->vehicle->model }}</td>
                                    <td><span class="badge badge-info" style="font-family:monospace;font-size:.85rem">{{ $reg->vehicle->plate_number }}</span></td>
                                    <td>{{ $reg->school_year }}</td>
                                    <td>
                                        @if($reg->documents->isNotEmpty())
                                            @foreach($reg->documents as $doc)
                                                <a href="{{ asset('storage/' . $doc->image_path) }}" target="_blank"
                                                   class="btn btn-xs btn-outline-secondary mb-1">
                                                    <i class="fas fa-file-image mr-1"></i>{{ $doc->document_type }}
                                                </a><br>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(strtolower((string) $reg->status) === 'approved')
                                            <span class="badge badge-success py-2 px-3"><i class="fas fa-check mr-1"></i>Approved</span>
                                        @elseif(strtolower((string) $reg->status) === 'pending')
                                            <span class="badge badge-warning text-dark py-2 px-3"><i class="fas fa-clock mr-1"></i>Pending</span>
                                        @elseif(strtolower((string) $reg->status) === 'rejected')
                                            <div>
                                                <span class="badge badge-danger py-2 px-3"><i class="fas fa-times mr-1"></i>Rejected</span>
                                                @if($reg->rejection_reason)
                                                    <div class="text-danger small mt-1">{{ Str::limit($reg->rejection_reason, 40) }}</div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reg->qr_sticker_id)
                                            <span class="badge badge-dark" style="font-family:monospace;font-size:.85rem;padding:5px 10px">
                                                <i class="fas fa-qrcode mr-1"></i>{{ $reg->qr_sticker_id }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── TAB 3: VIOLATIONS ───────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-violations">
        @if($violations->isEmpty())
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shield-alt fa-4x text-success mb-3"></i>
                    <h4 class="text-success font-weight-bold">Clean Record!</h4>
                    <p class="text-muted">No parking or security violations have been recorded against your vehicles.</p>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($violations as $violation)
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm" style="border-left:5px solid #dc3545">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2" style="gap:8px">
                                <span class="badge badge-danger text-uppercase" style="font-size:.8rem;padding:5px 10px">
                                    {{ Str::title(str_replace('_',' ', $violation->violation_type)) }}
                                </span>
                                <span class="text-muted small">{{ $violation->created_at->format('M d, Y · g:i A') }}</span>
                            </div>
                            <div class="font-weight-bold mb-1">
                                <i class="fas fa-car mr-2 text-muted"></i>
                                {{ $violation->vehicle->make }} {{ $violation->vehicle->model }}
                                <span class="badge badge-secondary ml-1" style="font-family:monospace">{{ $violation->vehicle->plate_number }}</span>
                            </div>
                            @if($violation->location_notes)
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $violation->location_notes }}
                                </p>
                            @endif
                            <div class="mt-2">
                                @if($violation->sanction_applied)
                                    <span class="badge py-2 px-3" style="background:#7b1113;color:#fff">
                                        <i class="fas fa-gavel mr-1"></i>Sanction Applied
                                    </span>
                                    @foreach($violation->sanctions->where('is_active', true) as $sanction)
                                        <div class="mt-1 small text-danger">
                                            <i class="fas fa-ban mr-1"></i>
                                            {{ $sanction->sanction_type }}
                                            @if($sanction->end_date)
                                                until {{ $sanction->end_date->format('M d, Y') }}
                                            @endif
                                            @if($sanction->description)
                                                <br><span class="text-muted">{{ $sanction->description }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="badge badge-warning text-dark py-2 px-3">
                                        <i class="fas fa-clock mr-1"></i>Under Review
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>{{-- /.tab-content --}}

@endsection

@section('scripts')
<script>
// ── Live preview before upload ─────────────────────────────────
function previewAndSubmit(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const thumb = document.getElementById('profileThumb');
        if (thumb.tagName === 'DIV') {
            const img = document.createElement('img');
            img.id = 'profileThumb';
            img.style.cssText = 'width:100%;height:100%;object-fit:cover';
            img.src = e.target.result;
            thumb.parentNode.replaceChild(img, thumb);
        } else {
            thumb.src = e.target.result;
        }
        document.getElementById('photoUploadForm').submit();
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Bootstrap tab active state styling ────────────────────────
document.querySelectorAll('#userTabs .nav-link').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('#userTabs .nav-link').forEach(function(t) {
            t.style.color = '#6c757d';
            t.style.borderBottom = 'none';
        });
        this.style.color = '#7b1113';
        this.style.borderBottom = '3px solid #7b1113';
    });
});

// Live GPS Tracking runs globally via the shared layout.

// Notifications are shown via the bell button (unread count + dropdown).
</script>
@endsection
