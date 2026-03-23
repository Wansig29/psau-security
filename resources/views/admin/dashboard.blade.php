@extends('layouts.adminlte')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Custom Tabs -->
        <div class="card card-maroon card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="admin-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-queue" data-toggle="pill" href="#content-queue" role="tab" aria-controls="content-queue" aria-selected="true">
                            <i class="fas fa-clipboard-check mr-1"></i> Registration Verification Queue
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-map" data-toggle="pill" href="#content-map" role="tab" aria-controls="content-map" aria-selected="false">
                            <i class="fas fa-map-marked-alt mr-1"></i> Campus Violation Map
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-stats" data-toggle="pill" href="#content-stats" role="tab" aria-controls="content-stats" aria-selected="false">
                            <i class="fas fa-chart-pie mr-1"></i> Violation Statistics
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="admin-tabs-content">
                    
                    <!-- TAB 1: Verification Queue -->
                    <div class="tab-pane fade show active" id="content-queue" role="tabpanel" aria-labelledby="tab-queue">
                        <p class="text-muted mb-4">Review pending vehicle registrations. Compare the extracted Plate Number against the uploaded OR/CR document, then Approve or Reject the application.</p>
                        
                        @if($pendingRegistrations->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>All caught up!</h5>
                                <p>No pending registrations in the queue.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-striped border">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Applicant</th>
                                            <th>Vehicle & Photo</th>
                                            <th>Verification Docs</th>
                                            <th>OCR Engine Result</th>
                                            <th style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingRegistrations as $reg)
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold">{{ $reg->user->name }}</div>
                                                    <div class="text-muted small">{{ $reg->user->email }}</div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($reg->vehicle->photo_path)
                                                            <div class="mr-3 rounded border shadow-sm overflow-hidden" style="width: 60px; height: 60px; flex-shrink: 0;">
                                                                <img src="{{ asset('storage/' . $reg->vehicle->photo_path) }}" alt="Vehicle" style="width: 100%; height: 100%; object-fit: cover;">
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="font-weight-bold">{{ $reg->vehicle->make }} {{ $reg->vehicle->model }}</div>
                                                            <div class="text-muted small">Color: {{ $reg->vehicle->color }}</div>
                                                            <span class="badge badge-info text-uppercase mt-1">{{ $reg->vehicle->plate_number }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    @if($reg->documents->isNotEmpty())
                                                        <div class="d-flex flex-column gap-1">
                                                            @foreach($reg->documents as $doc)
                                                                <a href="{{ asset('storage/' . $doc->image_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary text-left mb-1" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-file-image mr-1 text-maroon"></i> {{ $doc->document_type }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-danger small"><i class="fas fa-exclamation-triangle"></i> No Documents Uploaded</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    @php $orcrDoc = $reg->documents->where('document_type', 'OR/CR')->first(); @endphp
                                                    @if($orcrDoc && $orcrDoc->ocr_extracted_text)
                                                        @php $ocrData = json_decode($orcrDoc->ocr_extracted_text, true); @endphp
                                                        @if(isset($ocrData['plate_number']))
                                                            <div class="font-mono text-success font-weight-bold" style="font-size: 1.1rem;">
                                                                <i class="fas fa-robot"></i> {{ $ocrData['plate_number'] }}
                                                            </div>
                                                            <div class="small text-muted mt-1">
                                                                <a href="#" class="text-maroon" data-toggle="collapse" data-target="#ocrRaw-{{ $reg->id }}">View Raw Data</a>
                                                            </div>
                                                            <div class="collapse mt-2" id="ocrRaw-{{ $reg->id }}">
                                                                <div class="bg-dark text-success p-2 rounded small font-mono text-left" style="max-width: 200px; max-height: 100px; overflow-y: auto;">
                                                                    <pre class="m-0 text-success" style="font-size: 0.7rem;">{{ json_encode($ocrData, JSON_PRETTY_PRINT) }}</pre>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted small">OCR Pending or Failed</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted small">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <div class="btn-group">
                                                        <form method="POST" action="{{ route('admin.registration.approve', $reg->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success mr-1" title="Approve">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.registration.reject', $reg->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Reject this registration?')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div> <!-- /.tab-pane -->

                    <!-- TAB 2: Geolocation Map -->
                    <div class="tab-pane fade" id="content-map" role="tabpanel" aria-labelledby="tab-map">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 text-maroon font-weight-bold">Live Violation Heatmap</h5>
                            <span class="badge badge-danger">{{ $mapViolations->count() }} Total Infractions Logged</span>
                        </div>
                        <p class="text-muted">This map plots all recorded campus security violations that were tagged with GPS coordinates.</p>
                        
                        <div id="violationMap" class="map-wrapper"></div>
                    </div> <!-- /.tab-pane -->

                    <!-- TAB 3: Violation Statistics -->
                    <div class="tab-pane fade" id="content-stats" role="tabpanel" aria-labelledby="tab-stats">
                        <h5 class="mb-3 text-maroon font-weight-bold">Violation Types Breakdown</h5>
                        <p class="text-muted">A statistical overview of all campus infractions to help identify security trends.</p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-8 col-lg-6">
                                <canvas id="violationChart" style="min-height: 300px; max-height: 400px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div> <!-- /.tab-pane -->

                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>
@endsection

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Leaflet Map once the tab is shown (fixes Leaflet rendering issues inside hidden bootstrap tabs)
        let mapInitialized = false;
        
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            if (e.target.id === 'tab-map' && !mapInitialized) {
                initMap();
                mapInitialized = true;
            }
            if (e.target.id === 'tab-stats' && !chartInitialized) {
                initChart();
                chartInitialized = true;
            }
        });

        // Initialize Chart.js
        let chartInitialized = false;
        function initChart() {
            const ctx = document.getElementById('violationChart').getContext('2d');
            const rawStats = @json($violationStats);
            
            // Format labels by replacing underscores with spaces and title-casing
            const labels = Object.keys(rawStats).map(key => key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
            const data = Object.values(rawStats);

            // AdminLTE maroon color palette for the chart
            const backgroundColors = [
                '#7b1113', // Maroon
                '#a83234', // Lighter maroon
                '#4a0a0b', // Darker maroon
                '#e65c5c', // Reddish pink
                '#2c3e50', // Dark Blue/Grey
                '#f39c12', // Warning Yellow
                '#7f8c8d'  // Grey
            ];

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }

        function initMap() {
            // Default center: Pampanga State Agricultural University (approximate coords)
            const map = L.map('violationMap').setView([15.2155, 120.7303], 15);

            // Add standard OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            // Violation Data passed from PHP controller
            const violations = @json($mapViolations);
            
            // Custom red icon for violations
            const redIcon = new L.Icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            // Loop through violations and add markers
            if(violations.length > 0) {
                const markers = [];
                violations.forEach(function(violation) {
                    if(violation.gps_lat && violation.gps_lng) {
                        const marker = L.marker([violation.gps_lat, violation.gps_lng], {icon: redIcon}).addTo(map);
                        
                        // Format the violation type string
                        const violationType = violation.violation_type.replace(/_/g, ' ').toUpperCase();
                        const date = new Date(violation.created_at).toLocaleString();
                        
                        // Construct the Popup HTML
                        let popupContent = `
                            <div style="min-width: 200px;">
                                <h6 style="color: #941719; font-weight: bold; margin-bottom: 2px;">${violationType}</h6>
                                <p style="font-size: 12px; color: #666; margin-bottom: 8px;">${date}</p>
                                <div style="font-size: 13px;">
                                    <strong>Plate:</strong> ${violation.vehicle.plate_number}<br>
                                    <strong>Vehicle:</strong> ${violation.vehicle.make} ${violation.vehicle.model}<br>
                        `;
                        
                        if(violation.location_notes) {
                            popupContent += `<strong>Notes:</strong> <em>${violation.location_notes}</em><br>`;
                        }

                        popupContent += `</div></div>`;
                        marker.bindPopup(popupContent);
                        markers.push(marker);
                    }
                });

                // Auto-fit bounds if we have markers
                if(markers.length > 0) {
                    const group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }
        }
    });
</script>
@endsection
