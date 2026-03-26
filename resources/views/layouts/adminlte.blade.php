<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, shrink-to-fit=no">
    <meta name="theme-color" content="#7b1113">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PSAU Security">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PSAU Security') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { -webkit-overflow-scrolling: touch; -webkit-tap-highlight-color: transparent; }
        :root {
            --maroon: #7b1113; --maroon-dark: #5a0d0f; --maroon-light: #9b1224;
            --sidebar-w: 240px;
        }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; display: flex; overflow: hidden; height: 100vh; }

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
            display: flex; align-items: center; gap: 10px;
        }
        .brand-logo { display: flex; align-items: center; gap: 10px; flex: 1; }
        .brand-icon {
            width: 38px; height: 38px; background: rgba(255,255,255,0.12);
            border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
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
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; height: 100vh; overflow-y: auto; }
        .topbar {
            background: #fff; height: 60px; display: flex; align-items: center;
            justify-content: space-between; padding: 0 28px;
            border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }
        .topbar-title { font-size: 17px; font-weight: 700; color: #111827; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }

        .content { padding: 28px; flex: 1; }

        /* ── Notification Bell ── */
        .notif-bell { position: relative; cursor: pointer; }
        .notif-badge {
            position: absolute; top: -4px; right: -4px;
            background: #dc2626; color: #fff; font-size: 9px; font-weight: 700;
            width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        }

        .notif-dropdown {
            display: none;
            position: absolute;
            top: 42px;
            right: 0;
            width: 360px;
            max-width: calc(100vw - 28px);
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
            overflow: hidden;
            z-index: 9999;
        }
        .notif-dropdown.open { display: block; }
        .notif-dropdown-header {
            padding: 12px 14px;
            background: #fafafa;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }
        .notif-item {
            padding: 12px 14px;
            border-bottom: 1px solid #f3f4f6;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item-title {
            font-weight: 800;
            font-size: 12px;
            color: #111827;
            margin-bottom: 4px;
        }
        .notif-item-msg {
            color: #4b5563;
            font-size: 12px;
            line-height: 1.3;
            word-break: break-word;
        }
        .notif-item-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
        }
        .notif-empty {
            padding: 16px 14px;
            color: #6b7280;
            font-size: 13px;
        }

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
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; text-decoration: none; font-family: inherit; }
        .btn-primary { background: var(--maroon); color: #fff; box-shadow: 0 2px 4px rgba(123,17,19,0.2); }
        .btn-primary:hover { background: var(--maroon-light); transform: translateY(-1px); }
        .btn-success { background: #dcfce7; color: #166534; } .btn-success:hover { background: #16a34a; color: #fff; }
        .btn-danger  { background: #fee2e2; color: #991b1b; } .btn-danger:hover  { background: #dc2626; color: #fff; }
        .btn-gray    { background: #f3f4f6; color: #374151; } .btn-gray:hover    { background: #e5e7eb; }
        .btn-sm { padding: 6px 10px; font-size: 12px; border-radius: 6px; }

        /* ── Alerts ── */
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .alert-error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

        /* ── Misc ── */
        .map-wrapper { height: 500px; width: 100%; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .badge-success  { background: #dcfce7; color: #166534; }
        .badge-danger   { background: #fee2e2; color: #991b1b; }
        .badge-warning  { background: #fef9c3; color: #854d0e; }
        .badge-info     { background: #eff6ff; color: #1d4ed8; }
        .badge-secondary{ background: #f3f4f6; color: #374151; font-family: monospace; }

        /* ── Mobile hamburger & overlay ── */
        .menu-toggle { display: none; background: none; border: none; cursor: pointer; color: #111827; align-items: center; justify-content: center; padding: 4px; }
        .sidebar-close { display: none; background: rgba(255,255,255,0.12); border: none; border-radius: 6px; color: #fff; width: 28px; height: 28px; cursor: pointer; align-items: center; justify-content: center; flex-shrink: 0; margin-left: auto; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 150; }
        body.sidebar-open .sidebar-overlay { display: block; }
        body.sidebar-open .sidebar { transform: translateX(0); }

        @media (max-width: 768px) {
            .menu-toggle { display: flex; }
            .sidebar-close { display: flex; }
            .sidebar { transform: translateX(-100%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); max-width: 80vw; }
            .main { margin-left: 0; }
            .content { padding: 14px; }
            .topbar { padding: 0 14px; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
        }
    </style>

    @stack('styles')

    {{-- BFCache Back-Button Handler --}}
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
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
                <div class="brand-sub">
                    @if(Auth::user() && Auth::user()->role === 'security')
                        Security Portal
                    @else
                        My Account
                    @endif
                </div>
            </div>
        </div>
        <button class="sidebar-close" onclick="document.body.classList.remove('sidebar-open')">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
    </div>

    @if(Auth::user() && Auth::user()->role === 'security')
        <div class="sidebar-section">Security Tasks</div>
        <a class="nav-item {{ request()->routeIs('security.dashboard') ? 'active' : '' }}" href="{{ route('security.dashboard') }}">
            <span class="nav-icon">🛡️</span> Enforcement Panel
        </a>
    @else
        <div class="sidebar-section">My Account</div>
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <span class="nav-icon">🏠</span> My Dashboard
        </a>
        <a class="nav-item {{ request()->routeIs('user.info') ? 'active' : '' }}" href="{{ route('user.info') }}">
            <span class="nav-icon">🛠️</span> Update Info
        </a>
        <a class="nav-item {{ request()->routeIs('user.registration.*') ? 'active' : '' }}" href="{{ route('user.registration.create') }}">
            <span class="nav-icon">🚗</span> Register Vehicle
        </a>
    @endif

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ Str::limit(auth()->user()->name ?? 'User', 18) }}</div>
                <div class="user-role">
                    @if(Auth::user() && Auth::user()->role === 'security') Security Officer
                    @else Student / Faculty @endif
                </div>
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

{{-- ── Overlay ── --}}
<div class="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>

{{-- ── Main ── --}}
<div class="main">
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="menu-toggle" onclick="document.body.classList.toggle('sidebar-open')">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="topbar-title">@yield('title', 'Dashboard')</div>
        </div>
        <div class="topbar-right">
            {{-- Notifications Bell --}}
            @if(Auth::check())
                <div class="notif-bell" id="notifBell">
                    <svg width="20" height="20" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="notif-badge">{{ Auth::user()->unreadNotifications->count() }}</span>
                    @endif

                    @php
                        $unread = Auth::user()->unreadNotifications()->take(10)->get();
                    @endphp
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-dropdown-header">
                            @if($unread->count() > 0)
                                You have {{ $unread->count() }} new update(s)
                            @else
                                No new notifications
                            @endif
                        </div>

                        @if($unread->count() > 0)
                            @foreach($unread as $n)
                                @php
                                    $data = $n->data ?? [];
                                    $status = strtolower((string) ($data['status'] ?? ''));
                                    $message = (string) ($data['message'] ?? '');
                                    $title = $status === 'approved'
                                        ? 'Application Approved'
                                        : ($status === 'pending' ? 'Application Under Review' : ($status === 'rejected' ? 'Application Rejected' : 'Update'));
                                @endphp
                                <div class="notif-item">
                                    <div class="notif-item-title">{{ $title }}</div>
                                    <div class="notif-item-msg">{{ $message }}</div>
                                    <div class="notif-item-actions">
                                        <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-gray btn-sm" style="padding:6px 10px;">
                                                Mark as read
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="notif-empty">You’re all caught up.</div>
                        @endif
                    </div>
                </div>
            @endif
            <span style="font-size:13px;color:#6b7280;">{{ now()->format('M d, Y') }}</span>
        </div>
    </header>

    <div class="content">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success"><span>✅</span> {{ session('success') }}</div>
        @endif
        @if(session('status'))
            <div class="alert alert-success"><span>✅</span> {{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error"><span>❌</span> {{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning"><span>⚠️</span> {{ session('warning') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error" style="align-items:flex-start">
                <span>⚠️</span>
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        @yield('content')

    </div>
</div>

{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

@yield('scripts')
@stack('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bell = document.getElementById('notifBell');
        const dropdown = document.getElementById('notifDropdown');
        if (!bell || !dropdown) return;

        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('open');
        });

        document.addEventListener('click', function() {
            dropdown.classList.remove('open');
        });
    });
</script>

@if(Auth::check() && Auth::user()->role === 'vehicle_user')
<script>
    // ── Live GPS Tracking (runs on all pages for vehicle users) ──────────────
    (function initLiveGpsSync(){
        if (!('geolocation' in navigator)) return;
        if (window.isSecureContext === false) {
            // Geolocation is blocked on non-HTTPS contexts in most browsers.
            console.warn('Geolocation blocked: insecure context (use HTTPS or localhost).');
            return;
        }

        const url = '/user/location'; // Enforce relative path to avoid Mixed Content HTTP/HTTPS issues on proxy hosts
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let lastSentAt = 0;
        let lastLat = null;
        let lastLng = null;

        function send(lat, lng) {
            const now = Date.now();
            if (now - lastSentAt < 8000) return; // throttle
            if (lastLat === lat && lastLng === lng && now - lastSentAt < 15000) return;

            lastSentAt = now;
            lastLat = lat;
            lastLng = lng;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf || ''
                },
                body: JSON.stringify({ lat: lat, lng: lng }),
                keepalive: true
            })
            .then(res => {
                if (!res.ok) {
                    console.error('GPS sync failed with status:', res.status);
                    res.text().then(text => console.error('Server error details:', text));
                }
            })
            .catch(err => console.error('GPS sync network failed:', err));
        }

        const options = { enableHighAccuracy: true, maximumAge: 10000, timeout: 8000 };

        // Prefer watchPosition for continuous updates
        try {
            navigator.geolocation.watchPosition(
                (pos) => send(pos.coords.latitude, pos.coords.longitude),
                (err) => console.warn('GPS error:', err),
                options
            );
        } catch (e) {
            console.warn('watchPosition failed:', e);
        }

        // Also fire one immediate update
        navigator.geolocation.getCurrentPosition(
            (pos) => send(pos.coords.latitude, pos.coords.longitude),
            (err) => console.warn('GPS error:', err),
            options
        );
    })();
</script>
@endif
<script>
    /* ── Fix 6: Idle Session Timeout (15 minutes) ── */
    (function () {
        const IDLE_MS = 15 * 60 * 1000;
        let idleTimer;

        function resetIdle() {
            clearTimeout(idleTimer);
            idleTimer = setTimeout(function () {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('logout') }}';
                const csrf = document.createElement('input');
                csrf.type  = 'hidden';
                csrf.name  = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }, IDLE_MS);
        }

        ['mousemove','mousedown','keydown','touchstart','scroll','click']
            .forEach(evt => document.addEventListener(evt, resetIdle, true));

        resetIdle();
    })();
</script>
</body>
</html>
