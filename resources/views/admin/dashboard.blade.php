@extends('layouts.admin')

@section('title', 'Registration Verification Queue — PSAU Parking')

@section('topbar-title', 'Registration Verification Queue')

@section('topbar-right')
    @if($pendingRegistrations->count() > 0)
        <span class="badge-count">{{ $pendingRegistrations->count() }} Pending</span>
    @endif
@endsection

@section('content')
        {{-- Stat Cards --}}
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

        {{-- Tab Navigation --}}
        <div class="tab-nav">
            <span class="tab-btn active tab-pending"><i class="fas fa-hourglass-half"></i> Pending Reviews ({{ $pendingCount }})</span>
            <a class="tab-btn" href="{{ route('admin.approved.index') }}"><i class="fas fa-check-circle"></i> Approved</a>
            <a class="tab-btn" href="{{ route('admin.sanctions.index') }}"><i class="fas fa-balance-scale"></i> Violations & Sanctions</a>
        </div>

        {{-- Table --}}
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
                                            $docDefs = ['or'=>['OR'],'cr'=>['CR'],'cor'=>['COR'],'license'=>['Lic.'],'school_id'=>['ID'],'or_cr'=>['OR/CR']];
                                        @endphp
                                        @if($reg->documents->isNotEmpty())
                                            <div style="display:flex;flex-wrap:wrap;gap:6px">
                                                @foreach($docDefs as $type => $def)
                                                    @if($docMap->has($type))
                                                        @php $doc = $docMap[$type]; @endphp
                                                        <a href="{{ asset('storage/' . $doc->image_path) }}" target="_blank"
                                                           title="{{ $def[1] }}" style="display:flex;flex-direction:column;align-items:center;width:44px;text-decoration:none;">
                                                            <img src="{{ asset('storage/' . $doc->image_path) }}" alt="{{ $def[1] }}"
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
                                            <form method="POST" action="{{ route('admin.registration.approve', $reg->id) }}">
                                                @csrf
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
@endsection
