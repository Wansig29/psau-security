@extends('layouts.adminlte')

@section('title', 'Security Dashboard')

@section('content')

{{-- ── Alerts ─────────────────────────────────────────────────────────── --}}
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

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- SECURITY PROFILE HERO CARD                              --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-lg mb-4" style="border:none;overflow:visible">
    {{-- Maroon banner --}}
    <div style="height:120px;background:linear-gradient(135deg,#7b1113 0%,#b22222 60%,#c0392b 100%);
                position:relative;border-radius:4px 4px 0 0;overflow:hidden">
        <div style="position:absolute;top:-20px;right:-20px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.07)"></div>
        <div style="position:absolute;bottom:-30px;left:40%;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.05)"></div>
    </div>

    {{-- White body area --}}
    <div style="background:#fff;border:1px solid #e3e6f0;border-top:none;border-radius:0 0 4px 4px;padding:0 24px 20px">

        <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:-46px">
            {{-- Avatar --}}
            <div style="position:relative;flex-shrink:0">
                <div style="width:92px;height:92px;border-radius:50%;border:4px solid #fff;overflow:hidden;
                            background:#e9ecef;box-shadow:0 4px 15px rgba(0,0,0,0.2)">
                    @if(auth()->user()->profile_photo_path)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                             style="width:100%;height:100%;object-fit:cover" alt="Profile Photo">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;
                                    justify-content:center;background:linear-gradient(135deg,#1a1a2e,#2980b9)">
                            <span style="font-size:2.5rem;font-weight:900;color:#fff;text-transform:uppercase">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Action Button --}}
            <div style="padding-bottom:4px">
                <a href="{{ route('security.violation.create', ['vehicle_id' => '']) }}"
                   class="btn font-weight-bold"
                   style="background:#e67e22;color:#fff;border-radius:8px;padding:9px 20px;
                          box-shadow:0 3px 10px rgba(230,126,34,0.3);white-space:nowrap;font-size:.95rem">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Log Violation Manually
                </a>
            </div>
        </div>

        {{-- Name / Badges --}}
        <div style="padding-top:12px">
            <h2 style="font-size:1.45rem;font-weight:900;color:#1a1a2e;margin-bottom:2px;line-height:1.2">
                {{ auth()->user()->name }}
            </h2>
            <div style="color:#666;font-size:.87rem;margin-bottom:10px">
                <i class="fas fa-envelope mr-1"></i>{{ auth()->user()->email }}
            </div>
            <div class="d-flex flex-wrap" style="gap:6px;margin-bottom:8px">
                <span class="badge badge-pill" style="background:#1a1a2e;color:#fff;font-size:.76rem;padding:4px 10px">
                    <i class="fas fa-user-shield mr-1"></i>Security Officer
                </span>
                <span class="badge badge-pill" style="background:#fff3cd;color:#856404;border:1px solid #ffeeba;font-size:.76rem;padding:4px 10px">
                    <i class="fas fa-clipboard-list mr-1"></i>{{ $recentViolations->count() }} Total Logged
                </span>
                <span class="badge badge-pill" style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;font-size:.76rem;padding:4px 10px">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $recentViolations->where('created_at', '>=', now()->startOfDay())->count() }} Today
                </span>
                <span class="badge badge-pill" style="background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;font-size:.76rem;padding:4px 10px">
                    <i class="fas fa-map-marked-alt mr-1"></i>{{ $mapViolations->count() }} GPS Pins
                </span>
            </div>
        </div>
    </div>

    {{-- Tabs row --}}
    <div style="background:#fff;border:1px solid #e3e6f0;border-top:1px solid #dee2e6;border-radius:0 0 4px 4px;margin-top:4px">
        <ul class="nav nav-tabs border-0" id="securityTabs">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold px-4 py-3" data-toggle="tab" href="#tab-scanner"
                   style="color:#7b1113;border-bottom:3px solid #7b1113;border-top:none;border-left:none;border-right:none">
                    <i class="fas fa-camera mr-2"></i>Scanner
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-4 py-3 text-secondary" data-toggle="tab" href="#tab-violations"
                   style="border:none">
                    <i class="fas fa-history mr-2"></i>My Logs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-4 py-3 text-secondary" data-toggle="tab" href="#tab-map"
                   style="border:none">
                    <i class="fas fa-map-marked-alt mr-2"></i>Campus Map
                </a>
            </li>
        </ul>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TAB CONTENT                                              --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="tab-content">

    {{-- ── TAB 1: VEHICLE SCANNER ─────────────────────────── --}}
    <div class="tab-pane fade show active" id="tab-scanner">
        <div class="card card-outline shadow" style="border-top:4px solid #7b1113; border-radius:12px">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <div style="width:80px;height:80px;background:#f8f9fa;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto">
                        <i class="fas fa-qrcode fa-2x" style="color:#7b1113"></i>
                    </div>
                </div>
                <h3 class="font-weight-bold text-dark">Live Vehicle Check</h3>
                <p class="text-muted mb-4 px-3" style="font-size:1.1rem">Tap the Scan button to engage the live camera, or manually enter a license plate to verify the vehicle.</p>
                
                <form action="{{ route('security.search') }}" method="GET" class="mx-auto" style="max-width:550px">
                    <div class="input-group input-group-lg mb-2 shadow-lg rounded" style="border:1px solid #dee2e6">
                        <div class="input-group-prepend">
                            <button type="button" class="btn btn-lg font-weight-bold px-4" id="openScannerBtn"
                                style="background:#1a1a2e;color:#fff;border-radius:0.3rem 0 0 0.3rem" title="Open Camera Scanner">
                                <i class="fas fa-camera mr-2"></i>Scan
                            </button>
                        </div>
                        <input type="text"
                               class="form-control"
                               name="query"
                               id="searchInput"
                               placeholder="e.g. ABC-1234 or QR-XXX"
                               required autofocus
                               style="font-size:1.2rem;letter-spacing:1px;border:none;box-shadow:none">
                        <div class="input-group-append">
                            <button class="btn btn-lg text-white font-weight-bold px-4" type="submit"
                                style="background:#7b1113;border-radius:0 0.3rem 0.3rem 0">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── TAB 2: MY LOGGED VIOLATIONS ────────────────────── --}}
    <div class="tab-pane fade" id="tab-violations">
        <div class="card shadow" style="border-radius:12px;border-top:4px solid #e67e22">
            <div class="card-header bg-white border-bottom-0 pb-0 pt-4 px-4">
                <h4 class="font-weight-bold mb-0 text-dark"><i class="fas fa-history mr-2 text-warning"></i>Recent Violations</h4>
                <p class="text-muted small">Showing the violations you have logged across campus.</p>
            </div>
            <div class="card-body p-0">
                @if($recentViolations->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-check fa-4x mb-3" style="opacity:0.2"></i>
                        <h5>No violations logged yet.</h5>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4" style="font-size:.85rem;font-weight:600;color:#666">Date & Time</th>
                                    <th style="font-size:.85rem;font-weight:600;color:#666">License Plate</th>
                                    <th style="font-size:.85rem;font-weight:600;color:#666">Offense Type</th>
                                    <th style="font-size:.85rem;font-weight:600;color:#666">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentViolations as $v)
                                <tr>
                                    <td class="px-4" style="font-size:.9rem;white-space:nowrap">
                                        <div class="font-weight-bold">{{ $v->created_at->format('M d, Y') }}</div>
                                        <div class="text-muted small">{{ $v->created_at->format('g:i A') }}</div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-info shadow-sm" style="font-family:monospace;font-size:.85rem;padding:5px 8px">
                                            {{ $v->vehicle->plate_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-dark font-weight-bold" style="font-size:.9rem">
                                        {{ Str::title(str_replace('_',' ', $v->violation_type)) }}
                                    </td>
                                    <td class="align-middle">
                                        @if($v->sanction_applied)
                                            <span class="badge badge-danger px-3 py-2"><i class="fas fa-gavel mr-1"></i>Sanctioned</span>
                                        @else
                                            <span class="badge badge-secondary px-3 py-2"><i class="fas fa-clock mr-1"></i>Pending</span>
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

    {{-- ── TAB 3: CAMPUS GPS MAP ──────────────────────────── --}}
    <div class="tab-pane fade" id="tab-map">
        <div class="card shadow" style="border-radius:12px;border-top:4px solid #2980b9;overflow:hidden">
            <div class="card-header bg-white pb-3 pt-4 px-4 border-bottom">
                <h4 class="font-weight-bold mb-0"><i class="fas fa-map-marked-alt mr-2 text-info"></i>Live Incident Map</h4>
                <p class="text-muted small mb-0 mt-1">Click the red pins to view exactly where the specific violation occurred.</p>
            </div>
            <div class="card-body p-0">
                <div id="violationMap" style="height:600px;width:100%;z-index:1"></div>
            </div>
        </div>
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
    // ── Bootstrap tab active state styling ────────────────────────
    document.querySelectorAll('#securityTabs .nav-link').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('#securityTabs .nav-link').forEach(function(t) {
                t.style.color = '#6c757d';
                t.style.borderBottom = 'none';
            });
            this.style.color = '#7b1113';
            this.style.borderBottom = '3px solid #7b1113';
            
            // Fix Leaflet map blank screen bug when rendering inside hidden tab
            if (this.getAttribute('href') === '#tab-map') {
                setTimeout(() => { map.invalidateSize(); }, 200);
            }
        });
    });

    // ── Leaflet Map Setup ─────────────────────────────────────
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
