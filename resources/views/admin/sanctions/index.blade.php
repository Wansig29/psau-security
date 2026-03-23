@extends('layouts.adminlte')

@section('title', 'Violations & Sanctions')

@section('content')
<div class="row">
    <div class="col-12">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            </div>
        @endif

        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-gavel mr-2 text-danger"></i>Violations & Sanctions Management</h3>
                <div class="card-tools">
                    <span class="badge badge-danger">{{ $violations->total() }} Total</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($violations->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>No violations have been logged yet.</h5>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped border mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Vehicle / Owner</th>
                                    <th>Violation</th>
                                    <th>Logged By</th>
                                    <th>Photo</th>
                                    <th>Sanctions</th>
                                    <th style="width:180px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($violations as $violation)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $violation->created_at->format('M d, Y') }}</div>
                                            <div class="text-muted small">{{ $violation->created_at->format('g:i A') }}</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info text-uppercase mb-1" style="font-family:monospace">{{ $violation->vehicle->plate_number }}</span>
                                            <div class="text-muted small">{{ $violation->vehicle->make }} {{ $violation->vehicle->model }}</div>
                                            @if($violation->vehicle->user)
                                                <div class="text-muted small">{{ $violation->vehicle->user->name }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-warning text-dark">
                                                {{ Str::title(str_replace('_', ' ', $violation->violation_type)) }}
                                            </span>
                                            @if($violation->location_notes)
                                                <div class="text-muted small mt-1">📍 {{ Str::limit($violation->location_notes, 35) }}</div>
                                            @endif
                                        </td>
                                        <td class="small">{{ $violation->loggedBy?->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($violation->photo_path)
                                                <a href="{{ asset('storage/' . $violation->photo_path) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $violation->photo_path) }}"
                                                        style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6">
                                                </a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($violation->sanctions->isNotEmpty())
                                                @foreach($violation->sanctions as $sanction)
                                                    <div class="d-flex align-items-center flex-wrap mb-1" style="gap:4px">
                                                        {{-- Sanction type badge --}}
                                                        @if($sanction->sanction_type === 'Warning')
                                                            <span class="badge badge-warning text-dark">⚠️ Warning</span>
                                                        @elseif($sanction->sanction_type === 'Suspended')
                                                            <span class="badge" style="background:#ff8c00;color:#fff">🚫 Suspended</span>
                                                        @else
                                                            <span class="badge badge-danger">❌ Revoked</span>
                                                        @endif
                                                        {{-- Auto badge --}}
                                                        @if(isset($sanction->source) && $sanction->source === 'auto')
                                                            <span class="badge badge-secondary" style="font-size:9px" title="Auto-applied by system">AUTO</span>
                                                        @endif
                                                        {{-- Active/Lifted status and Lift button --}}
                                                        @if($sanction->is_active)
                                                            <span class="badge badge-success" style="font-size:9px">Active</span>
                                                            <form method="POST" action="{{ route('admin.sanctions.resolve', $sanction->id) }}" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-xs btn-outline-secondary"
                                                                    onclick="return confirm('Lift this sanction? This will remove the ban for this vehicle.')"
                                                                    style="font-size:10px;padding:1px 7px">
                                                                    <i class="fas fa-unlock-alt"></i> Lift
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-muted small"><s>Lifted</s></span>
                                                        @endif
                                                        {{-- Date range --}}
                                                        @if($sanction->start_date)
                                                            <div class="w-100 text-muted" style="font-size:10px">
                                                                {{ $sanction->start_date->format('M d') }}
                                                                @if($sanction->end_date) – {{ $sanction->end_date->format('M d, Y') }} @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted small font-italic">None yet</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- View Details Modal Trigger --}}
                                            <button type="button" class="btn btn-sm btn-outline-info btn-block mb-1"
                                                data-toggle="modal" data-target="#modal-violation-{{ $violation->id }}">
                                                <i class="fas fa-eye mr-1"></i> View Details
                                            </button>
                                            {{-- Assign Sanction Collapse --}}
                                            <button class="btn btn-sm btn-outline-danger btn-block"
                                                data-toggle="collapse" data-target="#sanction-{{ $violation->id }}">
                                                <i class="fas fa-gavel mr-1"></i> Assign Sanction
                                            </button>
                                        </td>
                                    </tr>
                                    {{-- Assign Sanction inline form --}}
                                    <tr class="collapse" id="sanction-{{ $violation->id }}">
                                        <td colspan="7" style="background:#fff5f5;" class="p-0">
                                            <div class="p-3">
                                                <p class="text-danger small font-weight-bold mb-2">
                                                    Assigning sanction for:
                                                    <span style="font-family:monospace">{{ $violation->vehicle->plate_number }}</span>
                                                    — {{ Str::title(str_replace('_', ' ', $violation->violation_type)) }}
                                                </p>
                                                <form method="POST" action="{{ route('admin.sanctions.store', $violation->id) }}"
                                                    class="form-inline flex-wrap" style="gap:10px">
                                                    @csrf
                                                    <div class="form-group mr-3 mb-2">
                                                        <label class="mr-2 font-weight-bold small">Type:</label>
                                                        <select name="sanction_type" class="form-control form-control-sm" required>
                                                            <option value="">-- Select --</option>
                                                            <option value="Warning">⚠️ Warning</option>
                                                            <option value="Suspended">🚫 Suspended</option>
                                                            <option value="Revoked">❌ Revoked</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group mr-3 mb-2">
                                                        <label class="mr-2 font-weight-bold small">Start:</label>
                                                        <input type="date" name="start_date" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="form-group mr-3 mb-2">
                                                        <label class="mr-2 font-weight-bold small">End:</label>
                                                        <input type="date" name="end_date" class="form-control form-control-sm">
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-danger mb-2">
                                                        <i class="fas fa-gavel mr-1"></i> Assign
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $violations->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- ============================================================ --}}
{{-- DETAIL MODALS (one per violation)                            --}}
{{-- ============================================================ --}}
@foreach($violations as $violation)
<div class="modal fade" id="modal-violation-{{ $violation->id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $violation->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#7b1113;color:#fff">
                <h5 class="modal-title" id="modalLabel-{{ $violation->id }}">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Violation Details —
                    <span style="font-family:monospace">{{ $violation->vehicle->plate_number }}</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    {{-- Left column --}}
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th class="bg-light" style="width:40%">Violation Type</th>
                                <td>
                                    <span class="badge badge-warning text-dark">
                                        {{ Str::title(str_replace('_', ' ', $violation->violation_type)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Date &amp; Time</th>
                                <td>{{ $violation->created_at->format('F d, Y') }}<br>
                                    <small class="text-muted">{{ $violation->created_at->format('g:i A') }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Plate Number</th>
                                <td><strong style="font-family:monospace;font-size:15px">{{ $violation->vehicle->plate_number }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Vehicle</th>
                                <td>{{ $violation->vehicle->make }} {{ $violation->vehicle->model }}<br>
                                    <small class="text-muted">{{ $violation->vehicle->color }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Owner</th>
                                <td>
                                    @if($violation->vehicle->user)
                                        {{ $violation->vehicle->user->name }}<br>
                                        <small class="text-muted">{{ $violation->vehicle->user->email }}</small>
                                    @else
                                        <span class="text-muted">Unregistered / Unknown</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Logged By</th>
                                <td>{{ $violation->loggedBy?->name ?? 'N/A' }}</td>
                            </tr>
                            @if($violation->gps_lat && $violation->gps_lng)
                            <tr>
                                <th class="bg-light">GPS Location</th>
                                <td>
                                    <a href="https://maps.google.com/?q={{ $violation->gps_lat }},{{ $violation->gps_lng }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Open in Maps
                                    </a><br>
                                    <small class="text-muted">{{ round($violation->gps_lat, 5) }}, {{ round($violation->gps_lng, 5) }}</small>
                                </td>
                            </tr>
                            @endif
                        </table>

                        @if($violation->location_notes)
                        <div class="form-group">
                            <label class="font-weight-bold small text-muted text-uppercase">Location &amp; Notes</label>
                            <div class="bg-light border rounded p-2 small" style="white-space:pre-wrap">{{ $violation->location_notes }}</div>
                        </div>
                        @endif
                    </div>

                    {{-- Right column: photo + sanctions history --}}
                    <div class="col-md-6">
                        @if($violation->photo_path)
                        <div class="mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Photo Evidence</label><br>
                            <a href="{{ asset('storage/' . $violation->photo_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $violation->photo_path) }}"
                                    class="img-fluid rounded border"
                                    style="max-height:220px;object-fit:cover;width:100%">
                            </a>
                            <small class="text-muted d-block mt-1">Click to open full size</small>
                        </div>
                        @else
                        <div class="text-muted small text-center py-3 mb-3 bg-light rounded border">
                            <i class="fas fa-camera fa-2x mb-2 d-block text-muted"></i>No photo evidence attached.
                        </div>
                        @endif

                        <label class="font-weight-bold small text-muted text-uppercase">Sanctions History</label>
                        @if($violation->sanctions->isNotEmpty())
                            @foreach($violation->sanctions as $sanction)
                            <div class="card card-body p-2 mb-2 {{ $sanction->is_active ? 'border-danger' : 'border-secondary' }}">
                                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:6px">
                                    <div>
                                        @if($sanction->sanction_type === 'Warning')
                                            <span class="badge badge-warning text-dark">⚠️ Warning</span>
                                        @elseif($sanction->sanction_type === 'Suspended')
                                            <span class="badge" style="background:#ff8c00;color:#fff">🚫 Suspended</span>
                                        @else
                                            <span class="badge badge-danger">❌ Revoked</span>
                                        @endif
                                        @if(isset($sanction->source) && $sanction->source === 'auto')
                                            <span class="badge badge-secondary" style="font-size:9px">AUTO</span>
                                        @endif
                                        @if($sanction->is_active)
                                            <span class="badge badge-success" style="font-size:9px">Active</span>
                                        @else
                                            <span class="badge badge-light text-muted" style="font-size:9px">Lifted</span>
                                        @endif
                                    </div>
                                    {{-- Admin can lift any active sanction --}}
                                    @if($sanction->is_active)
                                    <form method="POST" action="{{ route('admin.sanctions.resolve', $sanction->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-secondary"
                                            onclick="return confirm('Lift this sanction? This will remove the ban.')"
                                            style="font-size:11px;padding:2px 8px">
                                            <i class="fas fa-unlock-alt mr-1"></i> Lift Sanction
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                @if($sanction->description)
                                    <div class="text-muted small mt-1">{{ $sanction->description }}</div>
                                @endif
                                @if($sanction->start_date)
                                    <div class="text-muted" style="font-size:11px;margin-top:3px">
                                        📅 {{ $sanction->start_date->format('M d, Y') }}
                                        @if($sanction->end_date) → {{ $sanction->end_date->format('M d, Y') }} @endif
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        @else
                            <div class="text-muted small font-italic">No sanctions applied to this violation yet.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
