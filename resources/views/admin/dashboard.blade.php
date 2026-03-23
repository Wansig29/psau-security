<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — PSAU Parking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --maroon: #6b0a16; --maroon-dark: #4e0710; --maroon-light: #9b1224;
            --sidebar-w: 240px;
        }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w); background: var(--maroon-dark);
            display: flex; flex-direction: column; position: fixed;
            top: 0; left: 0; height: 100vh; z-index: 200;
            box-shadow: 2px 0 12px rgba(0,0,0,0.3);
        }
        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .brand-logo { display: flex; align-items: center; gap: 10px; }
        .brand-icon {
            width: 38px; height: 38px; background: rgba(255,255,255,0.12);
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
        }
        .brand-name { color: #fff; font-size: 14px; font-weight: 700; line-height: 1.2; }
        .brand-sub { color: rgba(255,255,255,0.5); font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sidebar-section { padding: 16px 12px 8px; color: rgba(255,255,255,0.35); font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .nav-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 16px;
            color: rgba(255,255,255,0.65); text-decoration: none; font-size: 13px; font-weight: 500;
            border-radius: 8px; margin: 1px 8px; transition: all 0.15s;
        }
        .nav-item:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .nav-item.active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 600; }
        .nav-item .nav-icon { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }
        .sidebar-footer {
            margin-top: auto; padding: 16px; border-top: 1px solid rgba(255,255,255,0.08);
        }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: rgba(255,255,255,0.2); display: flex; align-items: center;
            justify-content: center; font-weight: 700; color: #fff; font-size: 14px; flex-shrink: 0;
        }
        .user-name { color: #fff; font-size: 13px; font-weight: 600; }
        .user-role { color: rgba(255,255,255,0.45); font-size: 11px; }
        .logout-btn {
            display: flex; align-items: center; gap: 8px; width: 100%;
            background: rgba(255,255,255,0.07); border: none; border-radius: 8px;
            padding: 8px 12px; color: rgba(255,255,255,0.6); font-size: 12px; font-weight: 500;
            cursor: pointer; transition: all 0.15s; text-align: left; font-family: inherit;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.14); color: #fff; }

        /* ── Main ── */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar {
            background: #fff; height: 60px; display: flex; align-items: center;
            justify-content: space-between; padding: 0 28px;
            border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }
        .topbar-title { font-size: 17px; font-weight: 700; color: #111827; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .badge-count {
            background: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 700;
            padding: 2px 8px; border-radius: 999px;
        }

        .content { padding: 28px; flex: 1; }

        /* ── Stat cards ── */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card {
            background: #fff; border-radius: 14px; padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07); display: flex; align-items: center;
            gap: 14px; border-left: 4px solid transparent; transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
        .stat-card.c-maroon { border-color: var(--maroon); } .stat-icon.c-maroon { background: #fdf2f3; }
        .stat-card.c-green  { border-color: #16a34a; }      .stat-icon.c-green  { background: #f0fdf4; }
        .stat-card.c-blue   { border-color: #2563eb; }      .stat-icon.c-blue   { background: #eff6ff; }
        .stat-card.c-red    { border-color: #dc2626; }      .stat-icon.c-red    { background: #fef2f2; }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .stat-value { font-size: 26px; font-weight: 800; color: #111827; line-height: 1; }
        .stat-label { font-size: 12px; color: #6b7280; margin-top: 3px; }

        /* ── Tab nav ── */
        .tab-nav { display: flex; gap: 4px; margin-bottom: 20px; background: #fff; padding: 6px; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); width: fit-content; }
        .tab-btn {
            padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
            text-decoration: none; color: #6b7280; transition: all 0.15s; white-space: nowrap;
            display: flex; align-items: center; gap: 6px;
        }
        .tab-btn.active { background: var(--maroon); color: #fff; box-shadow: 0 2px 8px rgba(107,10,22,0.35); }
        .tab-btn:hover:not(.active) { background: #f3f4f6; color: #374151; }

        /* ── Card ── */
        .card { background: #fff; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); overflow: hidden; }
        .card-header {
            padding: 16px 22px; border-bottom: 1px solid #f3f4f6;
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        }
        .card-title { font-size: 14px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead th { background: #f9fafb; color: #6b7280; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; padding: 12px 16px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.12s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }
        td { padding: 14px 16px; vertical-align: middle; }

        /* ── Buttons ── */
        .btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 7px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; text-decoration: none; }
        .btn-success { background: #dcfce7; color: #166534; } .btn-success:hover { background: #16a34a; color: #fff; }
        .btn-danger  { background: #fee2e2; color: #991b1b; } .btn-danger:hover  { background: #dc2626; color: #fff; }

        /* ── Misc ── */
        .plate-tag { font-family: 'Courier New', monospace; font-size: 15px; font-weight: 800; background: #f3f4f6; border: 1px solid #e5e7eb; padding: 4px 10px; border-radius: 6px; letter-spacing: 1.5px; color: var(--maroon); }
        .empty-state { text-align: center; padding: 56px 24px; color: #9ca3af; }
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 18px; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .alert-error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
            .stat-grid { grid-template-columns: 1fr 1fr; }
            table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>

{{-- ── Sidebar ── --}}
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">
            <div class="brand-icon">
                <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <div>
                <div class="brand-name">PSAU Parking</div>
                <div class="brand-sub">Admin Portal</div>
            </div>
        </div>
    </div>

    <div class="sidebar-section">Main Menu</div>
    <a class="nav-item active" href="{{ route('admin.dashboard') }}">
        <span class="nav-icon">📋</span> Pending Reviews
        <span style="margin-left:auto;background:rgba(255,255,255,0.2);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:999px;">{{ $pendingRegistrations->count() }}</span>
    </a>
    <a class="nav-item" href="{{ route('admin.approved.index') }}">
        <span class="nav-icon">✅</span> Approved
    </a>
    <a class="nav-item" href="{{ route('admin.sanctions.index') }}">
        <span class="nav-icon">⚖️</span> Violations & Sanctions
    </a>
    <a class="nav-item" href="{{ route('admin.users.index') }}">
        <span class="nav-icon">👥</span> User Management
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ Str::limit(auth()->user()->name, 18) }}</div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Sign Out
            </button>
        </form>
    </div>
</aside>

{{-- ── Main Content ── --}}
<div class="main">
    <header class="topbar">
        <div class="topbar-title">Registration Verification Queue</div>
        <div class="topbar-right">
            @if($pendingRegistrations->count() > 0)
                <span class="badge-count">{{ $pendingRegistrations->count() }} Pending</span>
            @endif
            <span style="font-size:13px;color:#6b7280;">{{ now()->format('M d, Y') }}</span>
        </div>
    </header>

    <div class="content">

        {{-- Alerts --}}
        @if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-error">❌ {{ session('error') }}</div>@endif

        {{-- Stat Cards --}}
        @php
            $totalRegistrations  = \App\Models\Registration::count();
            $approvedCount       = \App\Models\Registration::where('status','approved')->count();
            $pendingCount        = $pendingRegistrations->count();
            $violationsCount     = \App\Models\Violation::count();
        @endphp
        <div class="stat-grid">
            <div class="stat-card c-maroon">
                <div class="stat-icon c-maroon">📋</div>
                <div><div class="stat-value">{{ $pendingCount }}</div><div class="stat-label">Pending Review</div></div>
            </div>
            <div class="stat-card c-green">
                <div class="stat-icon c-green">✅</div>
                <div><div class="stat-value">{{ $approvedCount }}</div><div class="stat-label">Approved</div></div>
            </div>
            <div class="stat-card c-blue">
                <div class="stat-icon c-blue">🚗</div>
                <div><div class="stat-value">{{ $totalRegistrations }}</div><div class="stat-label">Total Registrations</div></div>
            </div>
            <div class="stat-card c-red">
                <div class="stat-icon c-red">⚠️</div>
                <div><div class="stat-value">{{ $violationsCount }}</div><div class="stat-label">Violations Logged</div></div>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="tab-nav">
            <span class="tab-btn active">⏳ Pending Reviews ({{ $pendingCount }})</span>
            <a class="tab-btn" href="{{ route('admin.approved.index') }}">✅ Approved</a>
            <a class="tab-btn" href="{{ route('admin.sanctions.index') }}">⚖️ Violations & Sanctions</a>
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
                                            $docDefs = ['or'=>['📄','OR'],'cr'=>['📋','CR'],'cor'=>['🎓','COR'],'license'=>['🪪','Lic.'],'school_id'=>['🏫','ID'],'or_cr'=>['📄','OR/CR']];
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
                                                            <span style="font-size:9px;color:#6b7280;margin-top:2px;text-align:center">{{ $def[0] }} {{ $def[1] }}</span>
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
                                                <button type="submit" class="btn btn-success" style="width:100%">✅ Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.registration.reject', $reg->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" style="width:100%" onclick="return confirm('Reject this registration?')">❌ Reject</button>
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
    </div>
</div>

</body>
</html>
