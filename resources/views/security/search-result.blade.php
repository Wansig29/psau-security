@extends('layouts.adminlte')

@section('title', 'Vehicle Search Result')

@section('content')

@php
    $registration       = $vehicle->registrations->first();
    $allViolations      = $vehicle->violations ?? collect();
    $activeViolations   = $allViolations->where('sanction_applied', true);
    $totalViolations    = $allViolations->count();
    $isApproved         = $registration && $registration->status === 'approved';
    $isPending          = $registration && $registration->status === 'pending';
@endphp

{{-- ── Top Alert: Flagged vehicle ─────────────────────────────────────── --}}
@if($activeViolations->count() > 0)
<div class="alert alert-danger shadow d-flex align-items-center mb-4" style="border-left:6px solid #6b0000;font-size:1.05rem">
    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
    <div>
        <strong style="font-size:1.15rem">⚠️ FLAGGED VEHICLE</strong><br>
        This vehicle has <strong>{{ $activeViolations->count() }} active sanction(s)</strong>. Proceed with caution.
    </div>
</div>
@endif

<div class="row">

    {{-- ── LEFT: Vehicle Identity Card ─────────────────────────────────── --}}
    <div class="col-lg-5 mb-4">
        <div class="card shadow" style="border-top:5px solid #7b1113">
            <div class="card-header" style="background:#7b1113">
                <h3 class="card-title text-white font-weight-bold">
                    <i class="fas fa-car mr-2"></i>Vehicle Identity
                </h3>
            </div>
            <div class="card-body">

                {{-- Plate Number (large & easy to read) --}}
                <div class="text-center mb-3">
                    <div class="d-inline-block border border-dark rounded px-4 py-2"
                         style="font-family:monospace;font-size:2rem;font-weight:900;letter-spacing:6px;background:#f8f9fa">
                        {{ $vehicle->plate_number }}
                    </div>
                </div>

                {{-- Vehicle photo --}}
                <div class="text-center mb-3">
                    @if($vehicle->photo_path)
                        <img src="{{ asset('storage/' . $vehicle->photo_path) }}"
                            class="img-fluid rounded border shadow-sm"
                            style="max-height:200px;object-fit:cover;width:100%"
                            alt="Vehicle Photo">
                    @else
                        <div class="bg-light border rounded d-flex align-items-center justify-content-center"
                             style="height:140px;color:#aaa">
                            <div class="text-center">
                                <i class="fas fa-image fa-3x mb-1"></i><br>
                                <small>No vehicle photo</small>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Vehicle details table --}}
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th class="bg-light" style="width:35%">Make / Model</th>
                        <td><strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Color</th>
                        <td>{{ $vehicle->color }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Owner</th>
                        <td>
                            <strong>{{ $vehicle->user->name }}</strong><br>
                            <small class="text-muted">{{ $vehicle->user->email }}</small>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Total Violations</th>
                        <td>
                            @if($totalViolations > 0)
                                <span class="badge badge-danger">{{ $totalViolations }} violation(s)</span>
                            @else
                                <span class="badge badge-success">Clean Record</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: Status + Documents + Actions ──────────────────────────── --}}
    <div class="col-lg-7 mb-4">

        {{-- Registration Status Card --}}
        <div class="card shadow mb-3">
            <div class="card-header bg-light">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-id-card mr-2"></i>Registration Status
                </h3>
            </div>
            <div class="card-body p-3">
                @if($isApproved)
                    <div class="d-flex align-items-center p-3 rounded"
                         style="background:#d4edda;border:2px solid #28a745">
                        <i class="fas fa-check-circle fa-3x text-success mr-3"></i>
                        <div>
                            <div class="font-weight-bold text-success" style="font-size:1.15rem">✅ VALID ENTRY</div>
                            <div class="text-muted small">School Year: <strong>{{ $registration->school_year }}</strong></div>
                            @if($registration->qr_sticker_id)
                                <div class="mt-1">
                                    QR Sticker: <span class="badge badge-success font-mono" style="font-size:.85rem">{{ $registration->qr_sticker_id }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($isPending)
                    <div class="d-flex align-items-center p-3 rounded"
                         style="background:#fff3cd;border:2px solid #ffc107">
                        <i class="fas fa-clock fa-3x text-warning mr-3"></i>
                        <div>
                            <div class="font-weight-bold text-warning" style="font-size:1.15rem">⏳ PENDING APPROVAL</div>
                            <div class="text-muted small">Registration is awaiting admin review.</div>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-center p-3 rounded"
                         style="background:#f8d7da;border:2px solid #dc3545">
                        <i class="fas fa-times-circle fa-3x text-danger mr-3"></i>
                        <div>
                            <div class="font-weight-bold text-danger" style="font-size:1.15rem">❌ NOT REGISTERED</div>
                            <div class="text-muted small">This vehicle has no valid campus registration.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Violation History Card --}}
        @if($allViolations->count() > 0)
        <div class="card shadow mb-3" style="border-top:4px solid #dc3545">
            <div class="card-header bg-light">
                <h3 class="card-title font-weight-bold text-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>Violation History ({{ $totalViolations }})
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Offense</th>
                                <th>Sanction</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allViolations->sortByDesc('created_at') as $v)
                            <tr>
                                <td style="white-space:nowrap;font-size:.85rem">
                                    {{ $v->created_at->format('M d, Y') }}<br>
                                    <span class="text-muted" style="font-size:.75rem">{{ $v->created_at->format('g:i A') }}</span>
                                </td>
                                <td style="font-size:.85rem">
                                    <span class="badge badge-warning text-dark">
                                        {{ Str::title(str_replace('_',' ',$v->violation_type)) }}
                                    </span>
                                    @if($v->location_notes)
                                        <div class="text-muted" style="font-size:.75rem">{{ Str::limit($v->location_notes, 50) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($v->sanction_applied)
                                        <span class="badge badge-danger">Sanctioned</span>
                                    @else
                                        <span class="badge badge-secondary">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="callout callout-success mb-3">
            <i class="fas fa-shield-alt mr-2 text-success"></i>
            <strong>Clean Record</strong> — No violations have been logged for this vehicle.
        </div>
        @endif

        {{-- Documents Card --}}
        @if($registration && $registration->documents->isNotEmpty())
        <div class="card shadow mb-3">
            <div class="card-header bg-light">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-file-alt mr-2"></i>Verification Documents
                </h3>
            </div>
            <div class="card-body py-2">
                @foreach($registration->documents as $doc)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <i class="fas fa-file-image text-muted mr-2"></i>
                        <span class="font-weight-bold">{{ $doc->document_type }}</span>
                    </div>
                    <a href="{{ asset('storage/' . $doc->image_path) }}" target="_blank"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="row mt-2">
            <div class="col-6">
                <a href="{{ route('security.dashboard') }}"
                   class="btn btn-outline-secondary btn-block btn-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
            <div class="col-6">
                <a href="{{ route('security.violation.create', ['vehicle_id' => $vehicle->id, 'registration_id' => $registration ? $registration->id : null]) }}"
                   class="btn btn-danger btn-block btn-lg font-weight-bold">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Log Violation
                </a>
            </div>
        </div>

    </div>{{-- /.col --}}
</div>{{-- /.row --}}

@endsection
