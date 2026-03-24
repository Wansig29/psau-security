<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, shrink-to-fit=no">
    <meta name="theme-color" content="#6b0a16">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PSAU Admin">
    <style> html, body { -webkit-overflow-scrolling: touch; -webkit-tap-highlight-color: transparent; } </style>
    <title>@yield('title', 'Admin Dashboard — PSAU Parking')</title>
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
        .stat-card.c-maroon { border-color: var(--maroon); } .stat-icon.c-maroon { background: #fdf2f3; color: var(--maroon); }
        .stat-card.c-green  { border-color: #16a34a; }      .stat-icon.c-green  { background: #f0fdf4; color: #16a34a; }
        .stat-card.c-blue   { border-color: #2563eb; }      .stat-icon.c-blue   { background: #eff6ff; color: #2563eb; }
        .stat-card.c-red    { border-color: #dc2626; }      .stat-icon.c-red    { background: #fef2f2; color: #dc2626; }
        .stat-card.c-orange { border-color: #ea580c; }      .stat-icon.c-orange { background: #fff7ed; color: #ea580c; }
        .stat-card.c-gray   { border-color: #6b7280; }      .stat-icon.c-gray   { background: #f3f4f6; color: #6b7280; }
        
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
        .tab-btn.active.tab-pending { background: var(--maroon); box-shadow: 0 2px 8px rgba(107,10,22,0.35); }
        .tab-btn.active.tab-approved { background: #16a34a; box-shadow: 0 2px 8px rgba(22,163,74,0.35); }
        .tab-btn.active.tab-sanctions { background: #dc2626; box-shadow: 0 2px 8px rgba(220,38,38,0.35); }

        /* ── Card ── */
        .card { background: #fff; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); overflow: hidden; margin-bottom: 20px; }
        .card-header {
            padding: 16px 22px; border-bottom: 1px solid #f3f4f6;
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: #fafafa;
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
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 6px 14px; border-radius: 7px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; text-decoration: none; font-family: inherit; }
        .btn-success { background: #dcfce7; color: #166534; } .btn-success:hover { background: #16a34a; color: #fff; }
        .btn-danger  { background: #fee2e2; color: #991b1b; } .btn-danger:hover  { background: #dc2626; color: #fff; }
        .btn-red   { background: #fee2e2; color: #991b1b; } .btn-red:hover   { background: #dc2626; color: #fff; }
        .btn-gray  { background: #f3f4f6; color: #374151;  } .btn-gray:hover  { background: #e5e7eb; }
        .btn-primary { background: var(--maroon); color: #fff; box-shadow: 0 2px 4px rgba(107,10,22,0.2); }
        .btn-primary:hover { background: var(--maroon-light); transform: translateY(-1px); box-shadow: 0 4px 6px rgba(107,10,22,0.3); }
        .btn-sm { padding: 6px 10px; font-size: 12px; border-radius: 6px; }
        .btn-outline-danger { background: #fff; color: #dc2626; border: 1px solid #fca5a5; }
        .btn-outline-danger:hover { background: #fef2f2; border-color: #dc2626; }
        .btn-outline-primary { background: #fff; color: #2563eb; border: 1px solid #bfdbfe; }
        .btn-outline-primary:hover { background: #eff6ff; border-color: #2563eb; }

        /* ── Misc ── */
        .plate-tag { font-family: 'Courier New', monospace; font-size: 14px; font-weight: 800; background: #f3f4f6; border: 1px solid #e5e7eb; padding: 4px 10px; border-radius: 6px; letter-spacing: 1.5px; color: var(--maroon); display: inline-block;}
        .empty-state { text-align: center; padding: 56px 24px; color: #9ca3af; }
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .alert-error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }

        /* ── Badges ── */
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .badge-admin { background: #fce7f3; color: #be185d; border: 1px solid #fbcfe8; font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: 0.3px; }
        .badge-security { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: 0.3px; }
        .badge-user { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: 0.3px; }
        .badge-auto   { background: #e0f2fe; color: #0369a1; font-size: 9px; letter-spacing: 0.4px; padding: 2px 7px; text-transform: uppercase; font-weight: 700;}
        .badge-orange  { background: #ffedd5; color: #9a3412; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-suspend { background: #ffedd5; color: #9a3412; }
        .badge-revoke  { background: #fee2e2; color: #991b1b; }
        .badge-active  { background: #dcfce7; color: #166534; }
        .badge-secondary { background: #f3f4f6; color: #374151; font-family: monospace; }
        .badge-info { background: #eff6ff; color: #1d4ed8; }

        /* ── Forms ── */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 24px; }
        .form-group { margin-bottom: 4px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
        .form-control.form-control-sm { padding: 6px 10px; font-size: 12px; }
        .form-control:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107,10,22,0.1); }
        .form-select { width: 100%; padding: 10px 14px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; background-color: #fff; cursor: pointer; font-family: inherit; }
        .form-select:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107,10,22,0.1); }
        .form-footer { padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; text-align: right; }
        
        .form-inline { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .form-inline .form-group { margin-bottom: 0; display: flex; align-items: center; gap: 8px; }

        /* ── Special layouts for specifics ── */
        .sanction-inline { display: none; background: #fff1f2; border-top: 1px solid #fecdd3; padding: 16px; }
        .sanction-form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        .sanction-form label { display: block; font-size: 11px; font-weight: 600; color: #374151; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.4px; }
        .sanction-form select, .sanction-form input[type="date"] { border: 1.5px solid #d1d5db; border-radius: 7px; padding: 7px 11px; font-size: 12px; font-family: inherit; outline: none; transition: border-color 0.2s; }
        .sanction-meta { font-size: 12px; color: #7f1d1d; margin-bottom: 12px; font-weight: 500; }
        
        .detail-inline { display: none; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 20px; }
        .detail-inline.open { display: block; }
        .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
        .detail-item label { font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 3px; display: block; }
        .detail-item .detail-val { font-size: 13px; color: #1f2937; font-weight: 500; line-height: 1.5; }
        .detail-val.notes { white-space: pre-wrap; word-break: break-word; }

        .menu-toggle { display: none; background: none; border: none; cursor: pointer; color: #111827; align-items: center; justify-content: center; padding: 4px; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 150; transition: opacity 0.3s; }
        body.sidebar-open .sidebar-overlay { display: block; opacity: 1; }
        body.sidebar-open .sidebar { transform: translateX(0); }

        @media (max-width: 768px) {
            .menu-toggle { display: flex; }
            .sidebar { transform: translateX(-100%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
            .main { margin-left: 0; }
            .content { padding: 14px; }
            .topbar { padding: 0 14px; }
            .stat-grid { grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
            .stat-card { padding: 14px; gap: 10px; }
            .stat-value { font-size: 20px; }
            .form-grid { grid-template-columns: 1fr; padding: 16px; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            .tab-nav { overflow-x: auto; width: 100%; max-width: 100%; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
            .tab-nav::-webkit-scrollbar { display: none; }
            .topbar-right span { display: none; }
        }
    </style>
    @stack('styles')

    {{-- FIX 5 — BFCache Back-Button Handler --}}
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted ||
                (window.performance &&
                 window.performance.navigation &&
                 window.performance.navigation.type === 2))
            {
                window.location.reload(true);
            }
        });
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
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
    <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <span class="nav-icon">📋</span> Pending Reviews
        @php $pendingSideCount = \App\Models\Registration::where('status', 'pending')->count(); @endphp
        @if($pendingSideCount > 0)
            <span style="margin-left:auto;background:rgba(255,255,255,0.2);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:999px;">{{ $pendingSideCount }}</span>
        @endif
    </a>
    <a class="nav-item {{ request()->routeIs('admin.approved.*') ? 'active' : '' }}" href="{{ route('admin.approved.index') }}">
        <span class="nav-icon">✅</span> Approved
    </a>
    <a class="nav-item {{ request()->routeIs('admin.sanctions.*') ? 'active' : '' }}" href="{{ route('admin.sanctions.index') }}">
        <span class="nav-icon">⚖️</span> Violations & Sanctions
    </a>
    <a class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
        <span class="nav-icon">👥</span> User Management
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ Str::limit(auth()->user()->name ?? 'Administrator', 18) }}</div>
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

<div class="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>

{{-- ── Main Content ── --}}
<div class="main">
    <header class="topbar">
        <div style="display:flex; align-items:center; gap:12px;">
            <button class="menu-toggle" onclick="document.body.classList.toggle('sidebar-open')">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="topbar-title">@yield('topbar-title', 'Admin Portal')</div>
        </div>
        <div class="topbar-right">
            @yield('topbar-right')
            <span style="font-size:13px;color:#6b7280;">{{ now()->format('M d, Y') }}</span>
        </div>
    </header>

    <div class="content">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success"><span>✅</span> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error"><span>❌</span> {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error" style="align-items:flex-start">
                <span>⚠️</span> 
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @yield('content')

    </div>
</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('show');
        document.querySelector('.sidebar-overlay').classList.toggle('show');
    }
</script>
@stack('scripts')
</body>
</html>
