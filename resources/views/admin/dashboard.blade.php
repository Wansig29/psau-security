@extends('layouts.admin')

@section('title', 'Registration Verification Queue — PSAU Parking')

@section('topbar-title', 'Registration Verification Queue')

@section('topbar-right')
    @if($pendingRegistrations->count() > 0)
        <span class="badge-count">{{ $pendingRegistrations->count() }} Pending</span>
    @endif
@endsection

@section('content')
        @php
            $totalRegistrations  = \App\Models\Registration::count();
            $approvedCount       = \App\Models\Registration::whereRaw('LOWER(status) = ?', ['approved'])->count();
            $pendingCount        = $pendingRegistrations->count();
            $violationsCount     = \App\Models\Violation::count();
        @endphp
        <div class="stat-grid">
            <div class="stat-card c-maroon">
                <div class="stat-icon c-maroon"><i class="fas fa-clipboard-list"></i></div>
                <div><div class="stat-value">{{ $pendingCount }}</div><div class="stat-label">Pending Review</div></div>
            </div>
            <div class="stat-card c-green">
                <div class="stat-icon c-green"><i class="fas fa-check-circle"></i></div>
                <div><div class="stat-value">{{ $approvedCount }}</div><div class="stat-label">Approved</div></div>
            </div>
            <div class="stat-card c-blue">
                <div class="stat-icon c-blue"><i class="fas fa-car"></i></div>
                <div><div class="stat-value">{{ $totalRegistrations }}</div><div class="stat-label">Total Registrations</div></div>
            </div>
            <div class="stat-card c-red">
                <div class="stat-icon c-red"><i class="fas fa-exclamation-triangle"></i></div>
                <div><div class="stat-value">{{ $violationsCount }}</div><div class="stat-label">Violations Logged</div></div>
            </div>
        </div>

        <div class="tab-nav">
            <span class="tab-btn active tab-pending"><i class="fas fa-hourglass-half"></i> Pending Reviews ({{ $pendingCount }})</span>
            <a class="tab-btn" href="{{ route('admin.approved.index') }}"><i class="fas fa-check-circle"></i> Approved</a>
            <a class="tab-btn" href="{{ route('admin.sanctions.index') }}"><i class="fas fa-balance-scale"></i> Violations & Sanctions</a>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <svg width="16" height="16" fill="none" stroke="var(--maroon)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Pending Registrations — Review & Approve
                </div>
            </div>

            @if($pendingRegistrations->isEmpty())
                <div class="empty-state">
                    <svg width="48" height="48" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p style="font-size:15px;font-weight:600;color:#6b7280;">All caught up!</p>
                    <p style="font-size:13px;margin-top:4px;">No pending registrations in the queue.</p>
                </div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Vehicle</th>
                                <th>Plate No. (OCR)</th>
                                <th>Documents</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingRegistrations as $reg)
                                <tr>
                                    <td>
                                        <div style="font-weight:600;color:#111827">{{ $reg->user->name }}</div>
                                        <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $reg->user->email }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#111827">{{ $reg->vehicle->make }} {{ $reg->vehicle->model }}</div>
                                        <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $reg->vehicle->color }}</div>
                                    </td>
                                    <td><span class="plate-tag">{{ $reg->vehicle->plate_number }}</span></td>
                                    <td>
                                        @php
                                            $docMap = $reg->documents->keyBy('document_type');
                                            $docDefs = ['vehicle_photo'=>['Vehicle'],'or'=>['OR'],'cr'=>['CR'],'cor'=>['COR'],'license'=>['Lic.'],'school_id'=>['ID'],'or_cr'=>['OR/CR']];
                                        @endphp
                                        @if($reg->documents->isNotEmpty())
                                            <div style="display:flex;flex-wrap:wrap;gap:6px">
                                                @foreach($docDefs as $type => $def)
                                                    @if($docMap->has($type))
                                                        @php $doc = $docMap[$type]; @endphp
                                                        <a href="javascript:void(0)" onclick="openImageModal('{{ asset('storage/' . $doc->image_path) }}', '{{ $def[0] }}')"
                                                           title="{{ $def[0] }}" style="display:flex;flex-direction:column;align-items:center;width:44px;text-decoration:none;">
                                                            <img src="{{ asset('storage/' . $doc->image_path) }}" alt="{{ $def[0] }}"
                                                                 onerror="this.onerror=null;this.src='https://placehold.co/38x38?text={{ urlencode($def[0]) }}';"
                                                                 style="width:38px;height:38px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;transition:transform 0.2s"
                                                                 onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                                            <span style="font-size:9px;color:#6b7280;margin-top:2px;text-align:center">{{ $def[0] }}</span>
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span style="font-size:12px;color:#ef4444">No docs</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex;flex-direction:column;gap:6px">
                                            <form method="POST" action="{{ route('admin.registration.approve', $reg->id) }}" style="display:flex;flex-direction:column;gap:6px;margin-bottom:2px;">
                                                @csrf
                                                @if(Str::startsWith($reg->vehicle->plate_number, 'UNKNOWN_') || Str::startsWith($reg->vehicle->plate_number, 'PENDING_'))
                                                    <input type="text" name="corrected_plate" placeholder="Type verified plate no." required style="padding:6px;font-size:12px;border:1px solid #d1d5db;border-radius:4px;width:100%;box-sizing:border-box;">
                                                @endif
                                                <button type="submit" class="btn btn-success" style="width:100%"><i class="fas fa-check-circle"></i> Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.registration.reject', $reg->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" style="width:100%" onclick="return confirm('Reject this registration?')"><i class="fas fa-times-circle"></i> Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Image Modal Overlay --}}
        <div id="imageModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.85);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out;" onclick="this.style.display='none'">
            <div style="position:relative; max-width:90%; max-height:90%;">
                <div style="position:absolute;top:-40px;right:-10px;color:white;font-size:30px;cursor:pointer;">&times;</div>
                <img id="imageModalSrc" src="" style="max-width:100%;max-height:90vh;border-radius:8px;box-shadow:0 10px 25px rgba(0,0,0,0.5); object-fit:contain;">
            </div>
        </div>

        <script>
        function openImageModal(url, label) {
            document.getElementById('imageModalSrc').src = url;
            document.getElementById('imageModalSrc').onerror = function() {
                this.onerror = null;
                this.src = 'https://placehold.co/600x400?text=Image+Not+Found+(' + encodeURIComponent(label) + ')';
            };
            document.getElementById('imageModal').style.display = 'flex';
        }

        // Auto-refresh script for Pending Registrations
        document.addEventListener('DOMContentLoaded', function() {
            setInterval(() => {
                // Do not auto-refresh if the image modal is open
                if (document.getElementById('imageModal').style.display === 'flex') {
                    return; 
                }

                fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Cache-Control': 'no-cache'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Replace the Table Card
                    const newCard = doc.querySelector('.card');
                    if (newCard) {
                        document.querySelector('.card').innerHTML = newCard.innerHTML;
                    }

                    // Replace the Stats Grid
                    const newStats = doc.querySelector('.stat-grid');
                    if (newStats) {
                        document.querySelector('.stat-grid').innerHTML = newStats.innerHTML;
                    }

                    // Replace Tab Nav
                    const newTabs = doc.querySelector('.tab-nav');
                    if (newTabs) {
                        document.querySelector('.tab-nav').innerHTML = newTabs.innerHTML;
                    }

                    // Replace Topbar Badge
                    const newTopbar = doc.querySelector('.topbar-right');
                    if (newTopbar) {
                        document.querySelector('.topbar-right').innerHTML = newTopbar.innerHTML;
                    }
                })
                .catch(err => console.error('Auto-refresh error:', err));
            }, 10000); // Poll every 10 seconds
        });
        </script>
@endsection
