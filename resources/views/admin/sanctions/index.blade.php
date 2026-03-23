<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Violations & Sanctions — PSAU Parking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --maroon: #6b0a16; --maroon-dark: #4e0710; --sidebar-w: 240px; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; display: flex; min-height: 100vh; }
        .sidebar { width: var(--sidebar-w); background: var(--maroon-dark); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; z-index: 200; box-shadow: 2px 0 12px rgba(0,0,0,0.3); }
        .sidebar-brand { padding: 20px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .brand-logo { display: flex; align-items: center; gap: 10px; }
        .brand-icon { width: 38px; height: 38px; background: rgba(255,255,255,0.12); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .brand-name { color: #fff; font-size: 14px; font-weight: 700; } .brand-sub { color: rgba(255,255,255,0.5); font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sidebar-section { padding: 16px 12px 8px; color: rgba(255,255,255,0.35); font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: rgba(255,255,255,0.65); text-decoration: none; font-size: 13px; font-weight: 500; border-radius: 8px; margin: 1px 8px; transition: all 0.15s; }
        .nav-item:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .nav-item.active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 600; }
        .nav-icon { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }
        .sidebar-footer { margin-top: auto; padding: 16px; border-top: 1px solid rgba(255,255,255,0.08); }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-size: 14px; flex-shrink: 0; }
        .user-name { color: #fff; font-size: 13px; font-weight: 600; } .user-role { color: rgba(255,255,255,0.45); font-size: 11px; }
        .logout-btn { display: flex; align-items: center; gap: 8px; width: 100%; background: rgba(255,255,255,0.07); border: none; border-radius: 8px; padding: 8px 12px; color: rgba(255,255,255,0.6); font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.15s; text-align: left; font-family: inherit; }
        .logout-btn:hover { background: rgba(255,255,255,0.14); color: #fff; }
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 28px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
        .topbar-title { font-size: 17px; font-weight: 700; color: #111827; }
        .content { padding: 28px; flex: 1; }
        .tab-nav { display: flex; gap: 4px; margin-bottom: 24px; background: #fff; padding: 6px; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); width: fit-content; }
        .tab-btn { padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; color: #6b7280; transition: all 0.15s; white-space: nowrap; display: flex; align-items: center; gap: 6px; }
        .tab-btn.active { background: #dc2626; color: #fff; box-shadow: 0 2px 8px rgba(220,38,38,0.35); }
        .tab-btn:hover:not(.active) { background: #f3f4f6; color: #374151; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); overflow: hidden; }
        .card-header { padding: 16px 22px; border-bottom: 1px solid #f3f4f6; }
        .card-title { font-size: 14px; font-weight: 700; color: #111827; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead th { background: #f9fafb; color: #6b7280; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; padding: 12px 16px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.12s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }
        td { padding: 13px 16px; vertical-align: middle; }
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .badge-auto   { background: #e0f2fe; color: #0369a1; font-size: 9px; letter-spacing: 0.4px; padding: 2px 7px; }
        .badge-orange  { background: #ffedd5; color: #9a3412; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-suspend { background: #ffedd5; color: #9a3412; }
        .badge-revoke  { background: #fee2e2; color: #991b1b; }
        .badge-active  { background: #dcfce7; color: #166534; }
        .plate-tag { font-family: 'Courier New', monospace; font-size: 14px; font-weight: 800; background: #f3f4f6; border: 1px solid #e5e7eb; padding: 3px 9px; border-radius: 6px; letter-spacing: 1.5px; color: var(--maroon); display: inline-block; margin-bottom: 2px; }
        .btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 13px; border-radius: 7px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; text-decoration: none; font-family: inherit; }
        .btn-red   { background: #fee2e2; color: #991b1b; } .btn-red:hover   { background: #dc2626; color: #fff; }
        .btn-gray  { background: #f3f4f6; color: #374151;  } .btn-gray:hover  { background: #e5e7eb; }
        .sanction-inline { display: none; background: #fff1f2; border-top: 1px solid #fecdd3; padding: 16px; }
        .sanction-form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        label { display: block; font-size: 11px; font-weight: 600; color: #374151; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.4px; }
        select, input[type="date"] { border: 1.5px solid #d1d5db; border-radius: 7px; padding: 7px 11px; font-size: 12px; font-family: inherit; outline: none; transition: border-color 0.2s; }
        .sanction-meta { font-size: 12px; color: #7f1d1d; margin-bottom: 12px; font-weight: 500; }
        .detail-inline { display: none; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 20px; }
        .detail-inline.open { display: block; }
        .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
        .detail-item label { font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 3px; }
        .detail-item .detail-val { font-size: 13px; color: #1f2937; font-weight: 500; line-height: 1.5; }
        .detail-val.notes { white-space: pre-wrap; word-break: break-word; }
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 18px; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .alert-error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .empty-state { text-align: center; padding: 56px 24px; color: #9ca3af; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .main { margin-left: 0; } table { display: block; overflow-x: auto; } }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">
            <div class="brand-icon"><svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
            <div><div class="brand-name">PSAU Parking</div><div class="brand-sub">Admin Portal</div></div>
        </div>
    </div>
    <div class="sidebar-section">Main Menu</div>
    <a class="nav-item" href="{{ route('admin.dashboard') }}"><span class="nav-icon">📋</span> Pending Reviews</a>
    <a class="nav-item" href="{{ route('admin.approved.index') }}"><span class="nav-icon">✅</span> Approved</a>
    <a class="nav-item active" href="{{ route('admin.sanctions.index') }}"><span class="nav-icon">⚖️</span> Violations & Sanctions</a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div><div class="user-name">{{ Str::limit(auth()->user()->name, 18) }}</div><div class="user-role">Administrator</div></div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="logout-btn"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg> Sign Out</button>
        </form>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div class="topbar-title">Violations & Sanctions Management</div>
        <span style="font-size:13px;color:#6b7280">{{ now()->format('M d, Y') }}</span>
    </header>
    <div class="content">
        @if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-error">❌ {{ session('error') }}</div>@endif

        <div class="tab-nav">
            <a class="tab-btn" href="{{ route('admin.dashboard') }}">⏳ Pending Reviews</a>
            <a class="tab-btn" href="{{ route('admin.approved.index') }}">✅ Approved</a>
            <span class="tab-btn active">⚖️ Violations & Sanctions ({{ $violations->total() }})</span>
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
                                            <button class="btn" style="background:#eff6ff;color:#1d4ed8" onclick="toggleDetail({{ $violation->id }})">📄 View Details</button>
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
    </div>
</div>

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
</body>
</html>
