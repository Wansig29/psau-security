<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Accounts — PSAU Parking</title>
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

        .content { padding: 28px; flex: 1; }

        /* ── Stat cards ── */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card {
            background: #fff; border-radius: 14px; padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07); display: flex; align-items: center;
            gap: 14px; border-left: 4px solid transparent; transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
        .stat-card.c-maroon { border-color: var(--maroon); } .stat-icon.c-maroon { background: #fdf2f3; color: var(--maroon); }
        .stat-card.c-blue { border-color: #2563eb; } .stat-icon.c-blue { background: #eff6ff; color: #2563eb; }
        .stat-card.c-orange { border-color: #ea580c; } .stat-icon.c-orange { background: #fff7ed; color: #ea580c; }
        .stat-card.c-gray { border-color: #6b7280; } .stat-icon.c-gray { background: #f3f4f6; color: #6b7280; }
        
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .stat-value { font-size: 26px; font-weight: 800; color: #111827; line-height: 1; }
        .stat-label { font-size: 12px; color: #6b7280; margin-top: 3px; }

        /* ── Card ── */
        .card { background: #fff; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); overflow: hidden; margin-bottom: 24px; }
        .card-header {
            padding: 16px 22px; border-bottom: 1px solid #f3f4f6;
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: #fafafa;
        }
        .card-title { font-size: 15px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px; }

        /* ── Form ── */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 24px; }
        .form-group { margin-bottom: 4px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
        .form-control:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107,10,22,0.1); }
        .form-select { width: 100%; padding: 10px 14px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; background-color: #fff; cursor: pointer; font-family: inherit; }
        .form-select:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107,10,22,0.1); }
        .form-footer { padding: 16px 24px; background: #f9fafb; border-top: 1px solid #f3f4f6; text-align: right; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead th { background: #f9fafb; color: #6b7280; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; padding: 12px 16px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.12s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }
        td { padding: 14px 16px; vertical-align: middle; }

        /* ── Buttons ── */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; text-decoration: none; font-family: inherit; }
        .btn-primary { background: var(--maroon); color: #fff; box-shadow: 0 2px 4px rgba(107,10,22,0.2); }
        .btn-primary:hover { background: var(--maroon-light); transform: translateY(-1px); box-shadow: 0 4px 6px rgba(107,10,22,0.3); }
        .btn-sm { padding: 6px 10px; font-size: 12px; border-radius: 6px; }
        .btn-outline-danger { background: #fff; color: #dc2626; border: 1px solid #fca5a5; }
        .btn-outline-danger:hover { background: #fef2f2; border-color: #dc2626; }

        /* ── Badges ── */
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .badge-admin { background: #fce7f3; color: #be185d; border: 1px solid #fbcfe8; }
        .badge-security { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .badge-user { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }

        /* ── Misc ── */
        .alert { padding: 14px 18px; border-radius: 10px; font-size: 14px; font-weight: 500; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .alert-error { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
            .form-grid { grid-template-columns: 1fr; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
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
    <a class="nav-item" href="{{ route('admin.dashboard') }}">
        <span class="nav-icon">📋</span> Pending Reviews
    </a>
    <a class="nav-item" href="{{ route('admin.approved.index') }}">
        <span class="nav-icon">✅</span> Approved
    </a>
    <a class="nav-item" href="{{ route('admin.sanctions.index') }}">
        <span class="nav-icon">⚖️</span> Violations & Sanctions
    </a>
    <a class="nav-item active" href="{{ route('admin.users.index') }}">
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
        <div class="topbar-title">Secure User Account Management</div>
        <div class="topbar-right">
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
            <div class="alert alert-error">
                <span>⚠️</span> 
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Stat Cards --}}
        <div class="stat-grid">
            <div class="stat-card c-maroon">
                <div class="stat-icon c-maroon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div><div class="stat-value">{{ $totalUsers }}</div><div class="stat-label">Total Accounts</div></div>
            </div>
            <div class="stat-card c-blue">
                <div class="stat-icon c-blue">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div><div class="stat-value">{{ $admins }}</div><div class="stat-label">Administrators</div></div>
            </div>
            <div class="stat-card c-orange">
                <div class="stat-icon c-orange">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><div class="stat-value">{{ $officers }}</div><div class="stat-label">Security Officers</div></div>
            </div>
            <div class="stat-card c-gray">
                <div class="stat-icon c-gray">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div><div class="stat-value">{{ $regular }}</div><div class="stat-label">Standard Users</div></div>
            </div>
        </div>

        {{-- Create New User Form --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <svg width="18" height="18" fill="none" stroke="var(--maroon)" stroke-width="2" viewBox="0 0 24 24"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Manually Generate Secure Account
                </div>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. officer@psau.edu.ph" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Access Role</label>
                        <select name="role" class="form-select" required>
                            <option value="security">🛡️ Security Officer</option>
                            <option value="admin">⚙️ Campus Administrator</option>
                            <option value="vehicle_user">🚗 Standard User</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Initial Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                    </div>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Create Account
                    </button>
                </div>
            </form>
        </div>

        {{-- Existing Users Table --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Live Database Directory
                </div>
            </div>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>Account Name</th>
                            <th>Email Address</th>
                            <th>Role / Privilege</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td><strong style="color:#111827">{{ $user->name }}</strong></td>
                            <td style="color:#6b7280;font-size:12px">{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge badge-admin">Master Admin</span>
                                @elseif($user->role === 'security')
                                    <span class="badge badge-security">Security Guard</span>
                                @else
                                    <span class="badge badge-user">Standard User</span>
                                @endif
                            </td>
                            <td style="color:#6b7280;font-size:12px">
                                {{ $user->created_at->format('M d, Y - g:ia') }}
                            </td>
                            <td>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to completely delete this account? WARNING: ALL records linked to this user will be orphaned or deleted!')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                                @else
                                <span style="font-size:11px;color:#9ca3af;font-weight:600">Active (You)</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>
