@extends('layouts.adminlte')

@section('title', 'Security Enforcement Panel')

@section('content')

{{-- ── Stats Bar ─────────────────────────────────────────────────────── --}}
<div class="row mb-3">
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold">My Violations Today</span>
                <span class="info-box-number">
                    {{ $recentViolations->where('created_at', '>=', now()->startOfDay())->count() }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clipboard-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold">Total Logged (Me)</span>
                <span class="info-box-number">{{ $recentViolations->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-map-marked-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold">GPS-Tagged Violations</span>
                <span class="info-box-number">{{ $mapViolations->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon elevation-1" style="background:#7b1113"><i class="fas fa-user-shield"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold">Officer</span>
                <span class="info-box-number" style="font-size:1rem;line-height:1.6">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Alert messages ────────────────────────────────────────────────── --}}
@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-2"></i><strong>Done!</strong> {{ session('status') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
@endif

{{-- ── Two-column layout ─────────────────────────────────────────────── --}}
<div class="row">

    {{-- LEFT: Vehicle Search ──────────────────────────────────────────── --}}
    <div class="col-lg-5 mb-4">
        <div class="card card-outline shadow h-100" style="border-top:4px solid #7b1113">
            <div class="card-header" style="background:#7b1113">
                <h3 class="card-title text-white font-weight-bold">
                    <i class="fas fa-search mr-2"></i>Vehicle Check
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:0.95rem">
                    Enter a <strong>Plate Number</strong> or scan a <strong>QR Sticker ID</strong> to verify the vehicle.
                </p>
                <form action="{{ route('security.search') }}" method="GET">
                    <div class="input-group input-group-lg mb-3">
                        <input type="text"
                               class="form-control"
                               name="query"
                               id="searchInput"
                               placeholder="e.g. ABC-1234 or QR-XXXXX"
                               required autofocus
                               style="font-size:1.1rem;letter-spacing:1px">
                        <div class="input-group-append">
                            <button class="btn btn-lg text-white font-weight-bold" type="submit"
                                style="background:#7b1113;min-width:60px">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-muted small"><i class="fas fa-info-circle mr-1"></i>Partial matches are supported. Search is not case-sensitive.</p>
                </form>

                <hr>

                <div class="text-center">
                    <p class="text-muted small mb-2">Already know the vehicle? Go directly to:</p>
                    <a href="{{ route('security.violation.create', ['vehicle_id' => '']) }}"
                       class="btn btn-outline-danger btn-lg btn-block"
                       style="font-size:1rem;letter-spacing:.5px">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Log a New Violation
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Recent Violations ─────────────────────────────────────── --}}
    <div class="col-lg-7 mb-4">
        <div class="card card-outline shadow h-100" style="border-top:4px solid #e67e22">
            <div class="card-header" style="background:#e67e22">
                <h3 class="card-title text-white font-weight-bold">
                    <i class="fas fa-history mr-2"></i>My Recent Violations
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light badge-pill text-dark">Last {{ $recentViolations->count() }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($recentViolations->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-check fa-3x mb-3 text-success"></i>
                        <h6>No violations logged yet.</h6>
                        <p class="small">Violations you log will appear here.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="font-size:.8rem">Date</th>
                                    <th style="font-size:.8rem">Plate</th>
                                    <th style="font-size:.8rem">Offense</th>
                                    <th style="font-size:.8rem">Sanction</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentViolations as $v)
                                <tr>
                                    <td style="font-size:.85rem;white-space:nowrap">
                                        {{ $v->created_at->format('M d') }}<br>
                                        <span class="text-muted" style="font-size:.75rem">{{ $v->created_at->format('g:i A') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info" style="font-family:monospace;font-size:.8rem">
                                            {{ $v->vehicle->plate_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td style="font-size:.85rem">
                                        {{ Str::title(str_replace('_',' ', $v->violation_type)) }}
                                    </td>
                                    <td>
                                        @if($v->sanction_applied)
                                            <span class="badge badge-danger">Applied</span>
                                        @else
                                            <span class="badge badge-secondary">Pending</span>
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

</div>

{{-- ── Campus Violation Map ────────────────────────────────────────────── --}}
<div class="card card-outline shadow" style="border-top:4px solid #2980b9">
    <div class="card-header" style="background:#2980b9">
        <h3 class="card-title text-white font-weight-bold">
            <i class="fas fa-map-marked-alt mr-2"></i>Live Campus Violation Map
        </h3>
        <div class="card-tools">
            <span class="badge badge-light text-dark badge-pill">{{ $mapViolations->count() }} pins</span>
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <p class="text-muted small px-3 pt-2 mb-1">Click any red pin to see the violation details. Use this map to identify campus hotspots.</p>
        <div id="violationMap" style="height:480px;width:100%"></div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const map = L.map('violationMap').setView([15.2155, 120.7303], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    const redIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
    });

    const violations = @json($mapViolations);
    const markers = [];

    violations.forEach(function(v) {
        if (v.gps_lat && v.gps_lng) {
            const type = v.violation_type.replace(/_/g, ' ').toUpperCase();
            const date = new Date(v.created_at).toLocaleString();
            const popup = `
                <div style="min-width:200px;font-family:sans-serif">
                    <div style="background:#7b1113;color:#fff;padding:4px 8px;border-radius:4px;font-weight:bold;margin-bottom:6px;font-size:13px">
                        ⚠️ ${type}
                    </div>
                    <p style="font-size:11px;color:#888;margin-bottom:6px">${date}</p>
                    <div style="font-size:13px;line-height:1.5">
                        <strong>Plate:</strong> <span style="font-family:monospace">${v.vehicle.plate_number}</span><br>
                        <strong>Vehicle:</strong> ${v.vehicle.make} ${v.vehicle.model}
                        ${v.location_notes ? '<hr style="margin:6px 0"><em>' + v.location_notes + '</em>' : ''}
                    </div>
                </div>`;
            const marker = L.marker([v.gps_lat, v.gps_lng], {icon: redIcon}).addTo(map);
            marker.bindPopup(popup);
            markers.push(marker);
        }
    });

    if (markers.length > 0) {
        map.fitBounds(new L.featureGroup(markers).getBounds().pad(0.1));
    }
</script>
@endsection
