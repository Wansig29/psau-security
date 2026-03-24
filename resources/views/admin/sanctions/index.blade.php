@extends('layouts.admin')

@section('title', 'Violations & Sanctions — PSAU Parking')

@section('topbar-title', 'Violations & Sanctions Management')

@section('content')
        <div class="tab-nav">
            <a class="tab-btn" href="{{ route('admin.dashboard') }}">⏳ Pending Reviews</a>
            <a class="tab-btn" href="{{ route('admin.approved.index') }}">✅ Approved</a>
            <span class="tab-btn active tab-sanctions">⚖️ Violations & Sanctions ({{ $violations->total() }})</span>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">⚖️ All Logged Violations</div>
            </div>
            @if($violations->isEmpty())
                <div class="empty-state">
                    <div style="font-size:40px;margin-bottom:12px">✅</div>
                    <p style="font-size:14px;font-weight:600;color:#6b7280">No violations have been logged yet.</p>
                </div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Date Logged</th>
                                <th>Vehicle / Owner</th>
                                <th>Violation</th>
                                <th>Logged By</th>
                                <th>Photo</th>
                                <th>Sanctions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($violations as $violation)
                                <tr id="violation-{{ $violation->id }}">
                                    <td style="white-space:nowrap">
                                        <div style="font-weight:600;color:#111827">{{ $violation->created_at->format('M d, Y') }}</div>
                                        <div style="font-size:11px;color:#9ca3af">{{ $violation->created_at->format('g:i A') }}</div>
                                    </td>
                                    <td>
                                        <span class="plate-tag">{{ $violation->vehicle->plate_number }}</span>
                                        <div style="font-size:11px;color:#6b7280;margin-top:2px">{{ $violation->vehicle->make }} {{ $violation->vehicle->model }}</div>
                                        @if($violation->vehicle->user)<div style="font-size:11px;color:#9ca3af">{{ $violation->vehicle->user->name }}</div>@endif
                                    </td>
                                    <td>
                                        <span class="badge badge-orange">{{ Str::title(str_replace('_',' ',$violation->violation_type)) }}</span>
                                        @if($violation->location_notes)
                                            <div style="font-size:11px;color:#9ca3af;margin-top:4px">📍 {{ Str::limit($violation->location_notes, 28) }}</div>
                                            @if(strlen($violation->location_notes) > 28)
                                                <button onclick="toggleDetail({{ $violation->id }})" style="background:none;border:none;color:#2563eb;font-size:11px;cursor:pointer;padding:0;margin-top:3px;font-family:inherit;text-decoration:underline">more…</button>
                                            @endif
                                        @endif
                                    </td>
                                    <td style="font-size:12px;color:#374151">{{ $violation->loggedBy?->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($violation->photo_path)
                                            <a href="{{ asset('storage/' . $violation->photo_path) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $violation->photo_path) }}" style="width:44px;height:44px;object-fit:cover;border-radius:7px;border:1px solid #e5e7eb;transition:transform 0.2s" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                            </a>
                                        @else
                                            <span style="font-size:11px;color:#9ca3af">No photo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($violation->sanctions->isNotEmpty())
                                            <div style="display:flex;flex-direction:column;gap:4px">
                                                @foreach($violation->sanctions as $sanction)
                                                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                                        <span class="badge {{ $sanction->sanction_type==='Warning'?'badge-warning':($sanction->sanction_type==='Suspended'?'badge-suspend':'badge-revoke') }}">{{ $sanction->sanction_type }}</span>
                                                        @if(($sanction->source ?? 'manual') === 'auto')
                                                            <span class="badge badge-auto" title="Automatically applied">⚡ AUTO</span>
                                                        @endif
                                                        @if($sanction->is_active)
                                                            <span class="badge badge-active" style="font-size:10px">Active</span>
                                                            <form method="POST" action="{{ route('admin.sanctions.resolve', $sanction->id) }}" style="display:inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-gray" style="padding:2px 8px;font-size:10px" onclick="return confirm('Lift this sanction?')">Lift</button>
                                                            </form>
                                                        @else
                                                            <span style="font-size:10px;color:#9ca3af;text-decoration:line-through">Lifted</span>
                                                        @endif
                                                    </div>
                                                    @if($sanction->start_date)<div style="font-size:10px;color:#9ca3af">{{ $sanction->start_date->format('M d') }}@if($sanction->end_date) – {{ $sanction->end_date->format('M d, Y') }}@endif</div>@endif
                                                    @if(($sanction->source ?? 'manual') === 'auto' && $sanction->description)
                                                        <div style="font-size:10px;color:#64748b;font-style:italic;margin-top:2px">{{ $sanction->description }}</div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span style="font-size:12px;color:#9ca3af;font-style:italic">None yet</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex;flex-direction:column;gap:6px">
                                            <button class="btn btn-red" onclick="toggleSanction({{ $violation->id }})">⚖️ Assign Sanction</button>
                                            <button class="btn btn-outline-primary" style="background:#eff6ff;color:#1d4ed8" onclick="toggleDetail({{ $violation->id }})">📄 View Details</button>
                                        </div>
                                    </td>
                                </tr>
                                {{-- Details expandable row --}}
                                <tr>
                                    <td colspan="7" style="padding:0">
                                        <div class="detail-inline" id="detail-{{ $violation->id }}">
                                            <div class="detail-grid">
                                                <div class="detail-item">
                                                    <label>Violation Type</label>
                                                    <div class="detail-val">{{ Str::title(str_replace('_',' ',$violation->violation_type)) }}</div>
                                                </div>
                                                <div class="detail-item">
                                                    <label>Date &amp; Time</label>
                                                    <div class="detail-val">{{ $violation->created_at->format('F d, Y') }}<br><span style="color:#6b7280">{{ $violation->created_at->format('g:i A') }}</span></div>
                                                </div>
                                                <div class="detail-item">
                                                    <label>Vehicle</label>
                                                    <div class="detail-val">
                                                        <span style="font-family:'Courier New',monospace;font-weight:800;color:var(--maroon)">{{ $violation->vehicle->plate_number }}</span><br>
                                                        {{ $violation->vehicle->make }} {{ $violation->vehicle->model }} · {{ $violation->vehicle->color }}
                                                    </div>
                                                </div>
                                                @if($violation->vehicle->user)
                                                <div class="detail-item">
                                                    <label>Owner</label>
                                                    <div class="detail-val">{{ $violation->vehicle->user->name }}<br><span style="color:#6b7280;font-size:12px">{{ $violation->vehicle->user->email }}</span></div>
                                                </div>
                                                @endif
                                                <div class="detail-item">
                                                    <label>Logged By</label>
                                                    <div class="detail-val">{{ $violation->loggedBy?->name ?? 'N/A' }}</div>
                                                </div>
                                                @if($violation->gps_lat && $violation->gps_lng)
                                                <div class="detail-item">
                                                    <label>GPS Coordinates</label>
                                                    <div class="detail-val">
                                                        <a href="https://maps.google.com/?q={{ $violation->gps_lat }},{{ $violation->gps_lng }}" target="_blank" style="color:#2563eb;text-decoration:none">📍 {{ round($violation->gps_lat, 5) }}, {{ round($violation->gps_lng, 5) }}</a>
                                                    </div>
                                                </div>
                                                @endif
                                                @if($violation->location_notes)
                                                <div class="detail-item" style="grid-column:1/-1">
                                                    <label>Full Location &amp; Notes</label>
                                                    <div class="detail-val notes" style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;font-size:13px">{{ $violation->location_notes }}</div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                {{-- Sanction form row --}}
                                <tr>
                                    <td colspan="7" style="padding:0">
                                        <div class="sanction-inline" id="sanction-{{ $violation->id }}">
                                            <div class="sanction-meta">
                                                Assigning sanction for: <strong style="font-family:'Courier New',monospace">{{ $violation->vehicle->plate_number }}</strong> — {{ Str::title(str_replace('_',' ',$violation->violation_type)) }}
                                            </div>
                                            <form method="POST" action="{{ route('admin.sanctions.store', $violation->id) }}" class="sanction-form">
                                                @csrf
                                                <div>
                                                    <label>Sanction Type *</label>
                                                    <select name="sanction_type" required>
                                                        <option value="">-- Select --</option>
                                                        <option value="Warning">⚠️ Warning</option>
                                                        <option value="Suspended">🚫 Suspended</option>
                                                        <option value="Revoked">❌ Revoked</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label>Start Date</label>
                                                    <input type="date" name="start_date">
                                                </div>
                                                <div>
                                                    <label>End Date</label>
                                                    <input type="date" name="end_date">
                                                </div>
                                                <div style="display:flex;gap:8px">
                                                    <button type="submit" class="btn btn-red">Assign</button>
                                                    <button type="button" class="btn btn-gray" onclick="toggleSanction({{ $violation->id }})">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:16px 22px">{{ $violations->links() }}</div>
            @endif
        </div>
@endsection

@push('scripts')
<script>
function toggleSanction(id) {
    const el = document.getElementById('sanction-' + id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
function toggleDetail(id) {
    const el = document.getElementById('detail-' + id);
    el.classList.toggle('open');
}
</script>
@endpush
