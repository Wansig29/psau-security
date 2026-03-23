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
                        <div class="input-group-prepend">
                            <button type="button" class="btn btn-lg font-weight-bold" id="openScannerBtn"
                                style="background:#1a1a2e;color:#fff;border-radius:0.3rem 0 0 0.3rem" title="Open Camera Scanner">
                                <i class="fas fa-camera"></i> <span class="d-none d-sm-inline ml-1">Scan</span>
                            </button>
                        </div>
                        <input type="text"
                               class="form-control"
                               name="query"
                               id="searchInput"
                               placeholder="e.g. ABC-1234 or QR-XXXXX"
                               required autofocus
                               style="font-size:1.1rem;letter-spacing:1px;border-left:0;">
                        <div class="input-group-append">
                            <button class="btn btn-lg text-white font-weight-bold" type="submit"
                                style="background:#7b1113;min-width:60px">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-muted small"><i class="fas fa-info-circle mr-1"></i>Partial matches are supported. Tap <strong>Scan</strong> to use device camera.</p>
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

{{-- ── Camera Scanner Modal ────────────────────────────────────────────── --}}
<div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;border:none">
            <div class="modal-header" style="background:#1a1a2e;color:#fff;border:none">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-camera mr-2"></i>Live Vehicle Scanner</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="background:#000;position:relative">
                <div id="qr-reader" style="width:100%;min-height:300px"></div>
                
                {{-- UI Overlay for OCR --}}
                <div id="ocrOverlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);display:none;align-items:center;justify-content:center;color:#fff;flex-direction:column;z-index:999">
                    <div class="spinner-border text-light mb-3" role="status" style="width:3rem;height:3rem"></div>
                    <h5 class="font-weight-bold">AI Processing...</h5>
                    <p class="small text-center px-4" id="ocrStatusText">Extracting text from image...</p>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8f9fa;justify-content:center;flex-direction:column;gap:5px">
                <p class="text-muted small mb-1 w-100 text-center">
                    <strong>QR Codes</strong> are scanned automatically.<br>
                    Aim at a <strong>License Plate</strong> and tap below:
                </p>
                <button type="button" class="btn btn-lg btn-block font-weight-bold" id="capturePlateBtn"
                    style="background:#7b1113;color:#fff;border-radius:8px;box-shadow:0 4px 10px rgba(123,17,19,0.3)">
                    <i class="fas fa-object-group mr-2"></i>Capture License Plate
                </button>
            </div>
        </div>
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

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<script>
    // ── Scanner Logic ──
    let html5QrcodeScanner = null;

    $('#openScannerBtn').on('click', function() {
        $('#scannerModal').modal('show');
    });

    $('#scannerModal').on('shown.bs.modal', function () {
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5Qrcode("qr-reader");
        }
        
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess)
        .catch(err => {
            console.error("Camera start failed:", err);
            alert("Camera access denied or unavailable. Please ensure you are on HTTPS and have granted permission.");
        });
    });

    $('#scannerModal').on('hidden.bs.modal', function () {
        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            html5QrcodeScanner.stop().catch(console.error);
        }
        $('#ocrOverlay').css('display', 'none');
    });

    function onScanSuccess(decodedText) {
        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) html5QrcodeScanner.stop();
        $('#scannerModal').modal('hide');
        $('#searchInput').val(decodedText);
        $('#searchInput').closest('form').submit();
    }

    // ── OCR License Plate Capture ──
    $('#capturePlateBtn').on('click', async function() {
        const videoElement = document.querySelector('#qr-reader video');
        if (!videoElement) return;

        $('#ocrOverlay').css('display', 'flex');
        $('#ocrStatusText').text("Reading license plate...");

        const canvas = document.createElement('canvas');
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

        try {
            const result = await Tesseract.recognize(canvas, 'eng', {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        $('#ocrStatusText').text(`Analyzing AI: ${Math.round(m.progress * 100)}%`);
                    }
                }
            });
            
            const text = result.data.text.trim();
            const cleaned = text.replace(/[^A-Z0-9-]/gi, '').toUpperCase();
            
            // Match PH plates: 3 letters, 3-4 numbers
            const plateRegex = /[A-Z]{3}[-]?[0-9]{3,4}/;
            const match = cleaned.match(plateRegex);
            
            if (match) {
                onScanSuccess(match[0]);
            } else if (cleaned.length >= 4) {
               onScanSuccess(cleaned.substring(0, 8));
            } else {
                alert("Could not detect a clear license plate. Please try again.");
                $('#ocrOverlay').css('display', 'none');
            }
        } catch (err) {
            console.error(err);
            alert("OCR Processing failed.");
            $('#ocrOverlay').css('display', 'none');
        }
    });
</script>
@endsection
