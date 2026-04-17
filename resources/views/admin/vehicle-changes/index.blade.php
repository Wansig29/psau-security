@extends('layouts.admin')

@section('title', 'Vehicle Change Requests — PSAU Parking')

@section('topbar-title', 'Vehicle Change Requests')

@section('content')
    <div class="tab-nav">
        <a class="tab-btn" href="{{ route('admin.dashboard') }}"><i class="fas fa-hourglass-half"></i> Pending Reviews</a>
        <a class="tab-btn" href="{{ route('admin.approved.index') }}"><i class="fas fa-check-circle"></i> Approved</a>
        <a class="tab-btn" href="{{ route('admin.sanctions.index') }}"><i class="fas fa-balance-scale"></i> Violations & Sanctions</a>
        <span class="tab-btn active" style="border-bottom-color:#1d4ed8;color:#1d4ed8;">
            <i class="fas fa-exchange-alt"></i> Vehicle Changes
            @if($pendingCount > 0)<span style="margin-left:6px;background:#ef4444;color:#fff;border-radius:999px;padding:1px 7px;font-size:11px;font-weight:700;">{{ $pendingCount }}</span>@endif
        </span>
        <a class="tab-btn" href="{{ route('admin.statistics.index') }}"><i class="fas fa-chart-pie"></i> Statistics</a>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-exchange-alt"></i> All Vehicle Change Requests</div>
        </div>

        @if($requests->isEmpty())
            <div class="empty-state">
                <div style="font-size:40px;margin-bottom:12px;"><i class="fas fa-exchange-alt"></i></div>
                <p style="font-size:14px;font-weight:600;color:#6b7280">No vehicle change requests have been submitted yet.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Submitted</th>
                            <th>Requester</th>
                            <th>Current Vehicle</th>
                            <th>Requested Vehicle</th>
                            <th>Reason</th>
                            <th>Documents</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        <tr>
                            <td style="white-space:nowrap;">
                                <div style="font-weight:600;color:#111827;">{{ $req->created_at->format('M d, Y') }}</div>
                                <div style="font-size:11px;color:#9ca3af;">{{ $req->created_at->format('g:i A') }}</div>
                            </td>
                            <td>
                                <div style="font-weight:600;color:#111827;">{{ $req->user->name }}</div>
                                <div style="font-size:11px;color:#9ca3af;">{{ $req->user->email }}</div>
                            </td>
                            <td>
                                @if($req->oldVehicle)
                                    <span class="plate-tag">{{ $req->oldVehicle->plate_number }}</span>
                                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">{{ $req->oldVehicle->make }} {{ $req->oldVehicle->model }}</div>
                                @else
                                    <span style="color:#9ca3af;font-size:12px;">—</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight:600;color:#111827;">{{ $req->new_make }} {{ $req->new_model }}</div>
                                <div style="font-size:11px;color:#6b7280;">{{ $req->new_color }}</div>
                                @if($req->new_plate_number)
                                    <span class="plate-tag" style="margin-top:4px;display:inline-block;">{{ $req->new_plate_number }}</span>
                                @else
                                    <span style="font-size:10px;color:#f59e0b;font-style:italic;">OCR pending</span>
                                @endif
                            </td>
                            <td style="max-width:180px;font-size:12px;color:#374151;">
                                {{ Str::limit($req->reason, 60) }}
                                @if(strlen($req->reason) > 60)
                                    <button onclick="this.previousSibling.textContent='{{ addslashes($req->reason) }}';this.remove();"
                                            style="background:none;border:none;color:#2563eb;font-size:11px;cursor:pointer;padding:0;">more…</button>
                                @endif
                            </td>
                            <td>
                                @php
                                    $docTypes = ['vehicle_photo','or','cr','cor','license','school_id'];
                                    $docBlobs = $req->image_data ?? [];
                                    $docPaths = $req->document_paths ?? [];
                                @endphp
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    @foreach($docTypes as $dt)
                                        @if(!empty($docBlobs[$dt]))
                                            <a href="{{ route('admin.vehicle-changes.image', [$req->id, $dt]) }}" target="_blank"
                                               title="{{ ucwords(str_replace('_',' ',$dt)) }}"
                                               style="display:inline-block;width:38px;height:38px;border-radius:6px;overflow:hidden;border:1px solid #e5e7eb;">
                                                <img src="{{ route('admin.vehicle-changes.image', [$req->id, $dt]) }}"
                                                     style="width:100%;height:100%;object-fit:cover;"
                                                     onerror="this.parentElement.innerHTML='<span style=\'font-size:9px;color:#9ca3af;display:flex;align-items:center;justify-content:center;height:100%;\'>{{ strtoupper($dt) }}</span>'">
                                            </a>
                                        @elseif(!empty($docPaths[$dt]))
                                            <a href="{{ asset('storage/'.$docPaths[$dt]) }}" target="_blank"
                                               title="{{ ucwords(str_replace('_',' ',$dt)) }}"
                                               style="display:inline-block;width:38px;height:38px;border-radius:6px;overflow:hidden;border:1px solid #e5e7eb;">
                                                <img src="{{ asset('storage/'.$docPaths[$dt]) }}"
                                                     style="width:100%;height:100%;object-fit:cover;">
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($req->status === 'pending')
                                    <span class="badge badge-warning text-dark" style="padding:5px 10px;">Pending</span>
                                @elseif($req->status === 'approved')
                                    <span class="badge badge-active" style="padding:5px 10px;">Approved</span>
                                    @if($req->reviewedBy)<div style="font-size:10px;color:#6b7280;margin-top:3px;">by {{ $req->reviewedBy->name }}</div>@endif
                                @else
                                    <span class="badge badge-revoke" style="padding:5px 10px;">Rejected</span>
                                    @if($req->admin_notes)<div style="font-size:10px;color:#6b7280;margin-top:3px;font-style:italic;">{{ Str::limit($req->admin_notes, 40) }}</div>@endif
                                @endif
                            </td>
                            <td>
                                @if($req->status === 'pending')
                                <div style="display:flex;flex-direction:column;gap:6px;">
                                    <form method="POST" action="{{ route('admin.vehicle-changes.approve', $req->id) }}">
                                        @csrf
                                        <input type="hidden" name="admin_notes" value="Approved by admin.">
                                        <button type="submit" class="btn btn-success btn-sm"
                                                onclick="return confirm('Approve this vehicle change? The old registration will be revoked and a new one created.')"
                                                style="width:100%;">
                                            <i class="fas fa-check mr-1"></i> Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-red btn-sm" onclick="toggleRejectForm({{ $req->id }})" style="width:100%;">
                                        <i class="fas fa-times mr-1"></i> Reject
                                    </button>
                                </div>
                                {{-- Reject inline form --}}
                                <div id="reject-{{ $req->id }}" style="display:none;margin-top:8px;">
                                    <form method="POST" action="{{ route('admin.vehicle-changes.reject', $req->id) }}">
                                        @csrf
                                        <textarea name="admin_notes" rows="2" required
                                                  placeholder="Reason for rejection…"
                                                  style="width:100%;font-size:11px;padding:6px;border:1px solid #e5e7eb;border-radius:6px;resize:vertical;"></textarea>
                                        <div style="display:flex;gap:6px;margin-top:4px;">
                                            <button type="submit" class="btn btn-red btn-sm" style="font-size:11px;">Confirm Reject</button>
                                            <button type="button" class="btn btn-gray btn-sm" onclick="toggleRejectForm({{ $req->id }})" style="font-size:11px;">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                                @else
                                    <span style="font-size:11px;color:#9ca3af;">{{ $req->reviewed_at?->format('M d, Y') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 22px;">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function toggleRejectForm(id) {
    const el = document.getElementById('reject-' + id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
</script>
@endpush
