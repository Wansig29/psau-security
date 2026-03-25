@extends('layouts.adminlte')

@section('title', 'Vehicle Search Result')

@section('content')

@php
    $registration       = $vehicle->registrations->first();
    $allViolations      = $vehicle->violations ?? collect();
    $activeViolations   = $allViolations->where('sanction_applied', true);
    $totalViolations    = $allViolations->count();
    $statusLower        = $registration ? strtolower((string) $registration->status) : null;
    $isApproved         = $statusLower === 'approved';
    $isPending          = $statusLower === 'pending';
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

                <div class="text-center mb-3">
                    <div class="d-inline-block border border-dark rounded px-4 py-2"
                         style="font-family:monospace;font-size:2rem;font-weight:900;letter-spacing:6px;background:#f8f9fa">
                        {{ $vehicle->plate_number }}
                    </div>
                </div>

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
                            <div class="d-flex align-items-start" style="gap:10px;">
                                @if(!empty($vehicle->user->profile_photo_path))
                                    <img src="{{ asset('storage/' . $vehicle->user->profile_photo_path) }}"
                                         alt="Owner Photo"
                                         style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:1px solid #e5e7eb;flex-shrink:0;">
                                @else
                                    <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:40px;height:40px;flex-shrink:0;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                @endif

                                <div>
                                    <strong>{{ $vehicle->user->name }}</strong><br>
                                    <small class="text-muted">{{ $vehicle->user->email }}</small>

                                    @if(!empty($vehicle->user->contact_number))
                                        @php $phoneHref = preg_replace('/\s+/', '', $vehicle->user->contact_number); @endphp
                                        <div class="mt-1">
                                            <a href="tel:{{ $phoneHref }}"
                                               class="text-primary font-weight-bold small"
                                               style="text-decoration:none;">
                                                <i class="fas fa-phone-alt mr-1"></i>{{ $vehicle->user->contact_number }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
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

        <div class="card shadow mb-3">
            <div class="card-header bg-light">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-id-card mr-2"></i>Registration Status
                </h3>
            </div>
            <div class="card-body p-3">
                @if($isApproved)
                    <div class="d-flex align-items-center p-3 rounded" style="background:#d4edda;border:2px solid #28a745">
                        <i class="fas fa-check-circle fa-3x text-success mr-3"></i>
                        <div>
                            <div class="font-weight-bold text-success" style="font-size:1.15rem">✅ VALID ENTRY</div>
                            <div class="text-muted small">School Year: <strong>{{ $registration->school_year }}</strong></div>
                            @if($registration->qr_sticker_id)
                                <div class="mt-1">QR Sticker: <span class="badge badge-success font-mono" style="font-size:.85rem">{{ $registration->qr_sticker_id }}</span></div>
                            @endif
                        </div>
                    </div>
                @elseif($isPending)
                    <div class="d-flex align-items-center p-3 rounded" style="background:#fff3cd;border:2px solid #ffc107">
                        <i class="fas fa-clock fa-3x text-warning mr-3"></i>
                        <div>
                            <div class="font-weight-bold text-warning" style="font-size:1.15rem">⏳ PENDING APPROVAL</div>
                            <div class="text-muted small">Registration is awaiting admin review.</div>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-center p-3 rounded" style="background:#f8d7da;border:2px solid #dc3545">
                        <i class="fas fa-times-circle fa-3x text-danger mr-3"></i>
                        <div>
                            <div class="font-weight-bold text-danger" style="font-size:1.15rem">❌ NOT REGISTERED</div>
                            <div class="text-muted small">This vehicle has no valid campus registration.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Live Map --}}
        <div class="card shadow mb-3" style="border-top:4px solid #17a2b8">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-info"><i class="fas fa-map-marker-alt mr-2"></i>Live Location</h3>
                <span id="location-status" class="badge badge-secondary">Checking...</span>
            </div>
            <div class="card-body p-0">
                <div id="live-map" style="height: 300px; width: 100%; background: #e9ecef; display: flex; flex-direction:column; align-items: center; justify-content: center; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                    <small>Connecting to GPS...</small>
                </div>
            </div>
        </div>

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
                            <tr><th>Date</th><th>Offense</th><th>Sanction</th></tr>
                        </thead>
                        <tbody>
                            @foreach($allViolations->sortByDesc('created_at') as $v)
                            <tr>
                                <td style="white-space:nowrap;font-size:.85rem">
                                    {{ $v->created_at->format('M d, Y') }}<br>
                                    <span class="text-muted" style="font-size:.75rem">{{ $v->created_at->format('g:i A') }}</span>
                                </td>
                                <td style="font-size:.85rem">
                                    <span class="badge badge-warning text-dark">{{ Str::title(str_replace('_',' ',$v->violation_type)) }}</span>
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

        @if($registration && $registration->documents->isNotEmpty())
        <div class="card shadow mb-3">
            <div class="card-header bg-light">
                <h3 class="card-title font-weight-bold"><i class="fas fa-file-alt mr-2"></i>Verification Documents</h3>
            </div>
            <div class="card-body py-2">
                @foreach($registration->documents as $doc)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div><i class="fas fa-file-image text-muted mr-2"></i><span class="font-weight-bold">{{ $doc->document_type }}</span></div>
                    <a href="{{ asset('storage/' . $doc->image_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="row mt-2">
            <div class="col-6">
                <a href="{{ route('security.dashboard') }}" class="btn btn-outline-secondary btn-block btn-lg">
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

    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let map = null;
    let marker = null;
    let guardMarker = null;
    let routingControl = null;
    
    let guardLat = null;
    let guardLng = null;
    let targetLat = null;
    let targetLng = null;

    const userId = {{ $vehicle->user->id }};
    const mapContainer = document.getElementById('live-map');
    const statusBadge = document.getElementById('location-status');

    // Create custom dynamic markers for the map
    const onlineIcon = L.divIcon({
        className: 'custom-pin',
        html: '<div style="background-color: #28a745; width: 18px; height: 18px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>',
        iconSize: [18, 18],
        iconAnchor: [9, 9]
    });

    const offlineIcon = L.divIcon({
        className: 'custom-pin',
        html: '<div style="background-color: #dc3545; width: 18px; height: 18px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>',
        iconSize: [18, 18],
        iconAnchor: [9, 9]
    });

    function updateRoute() {
        if (!map || !guardLat || !guardLng || !targetLat || !targetLng) return;

        if (routingControl) {
            routingControl.setWaypoints([
                L.latLng(guardLat, guardLng),
                L.latLng(targetLat, targetLng)
            ]);
        } else {
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(guardLat, guardLng),
                    L.latLng(targetLat, targetLng)
                ],
                routeWhileDragging: false,
                addWaypoints: false,
                show: false, // Hide turn-by-turn text box
                lineOptions: {
                    styles: [{color: '#007bff', opacity: 0.8, weight: 6}] // Waze-style thick blue line
                },
                createMarker: function() { return null; } // Prevent default markers since we use custom
            }).addTo(map);
        }
    }

    // Guard's own live tracking
    if ("geolocation" in navigator) {
        navigator.geolocation.watchPosition(
            (pos) => {
                guardLat = pos.coords.latitude;
                guardLng = pos.coords.longitude;
                
                if (map && !guardMarker) {
                    guardMarker = L.circleMarker([guardLat, guardLng], {
                        radius: 8, fillColor: "#007bff", color: "#fff", weight: 3, opacity: 1, fillOpacity: 1
                    }).bindPopup("<b>Your Location</b>").addTo(map);
                } else if (guardMarker) {
                    guardMarker.setLatLng([guardLat, guardLng]);
                }
                updateRoute();
            },
            (err) => console.log("Guard GPS: ", err),
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    }

    function fetchLocation() {
        fetch(`/security/user-location/${userId}?t=${Date.now()}`)
            .then(res => res.json())
            .then(data => {
                if (data.lat && data.lng) {
                    targetLat = data.lat;
                    targetLng = data.lng;

                    const iconToUse = data.is_online ? onlineIcon : offlineIcon;
                    const timestampDisplay = data.last_seen_time || data.last_update;
                    const popupHTML = `<b>{{ addslashes($vehicle->user->name) }}</b><br>
                                       <span style="color: ${data.is_online ? '#28a745' : '#dc3545'}">
                                       ${data.is_online ? '🟢 Live' : '🔴 Offline'}
                                       </span><br>
                                       <small>Seen: ${timestampDisplay}</small>`;

                    if (data.is_online) {
                        statusBadge.className = 'badge badge-success';
                        statusBadge.textContent = 'Live (' + data.last_update + ')';
                    } else {
                        statusBadge.className = 'badge badge-danger'; // Changed from warning to danger
                        statusBadge.textContent = 'Offline (Last seen: ' + timestampDisplay + ')';
                    }

                    if (!map) {
                        mapContainer.innerHTML = ''; // Clear loading spinner
                        map = L.map('live-map').setView([data.lat, data.lng], 16);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap'
                        }).addTo(map);
                        marker = L.marker([data.lat, data.lng], {icon: iconToUse}).addTo(map);
                        marker.bindPopup(popupHTML).openPopup();
                        
                        // If guard location was fetched before map initialized
                        if (guardLat && guardLng && !guardMarker) {
                            guardMarker = L.circleMarker([guardLat, guardLng], {
                                radius: 8, fillColor: "#007bff", color: "#fff", weight: 3, opacity: 1, fillOpacity: 1
                            }).bindPopup("<b>Your Location</b>").addTo(map);
                        }
                    } else {
                        const newLatLng = new L.LatLng(data.lat, data.lng);
                        marker.setLatLng(newLatLng);
                        marker.setIcon(iconToUse); // Update color dynamically
                        
                        // Only pan map if no routing is active to prevent fighting the route bounding box
                        if (!routingControl) {
                            map.panTo(newLatLng);
                        }
                        marker.setPopupContent(popupHTML);
                    }
                    
                    updateRoute();
                } else {
                    statusBadge.className = 'badge badge-secondary';
                    statusBadge.textContent = 'No Data';
                    mapContainer.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100"><i class="fas fa-map-marker-slash fa-2x mb-2 text-muted"></i><div>Location sharing disabled by user</div></div>';
                }
            })
            .catch(err => {
                console.error('Error fetching location', err);
                statusBadge.className = 'badge badge-danger';
                statusBadge.textContent = 'Error fetching data';
            });
    }

    fetchLocation();
    setInterval(fetchLocation, 10000); // Poll every 10 seconds
});
</script>

@endsection
