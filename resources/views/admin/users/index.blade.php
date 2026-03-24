@extends('layouts.admin')

@section('title', 'User Accounts — PSAU Parking')

@section('topbar-title', 'Secure User Account Management')

@section('content')
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
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div class="card-title" style="margin:0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:6px; vertical-align:middle;"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Live Database Directory
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <input type="text" id="userDirectorySearch" placeholder="🔍 Search by name, email, or role..." style="padding:6px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; width:220px; outline:none; transition:border 0.2s;" onfocus="this.style.borderColor='var(--maroon)'" onblur="this.style.borderColor='#d1d5db'">
                    <button type="button" id="toggleDirBtn" onclick="toggleUsersDirectory()" style="background:#f3f4f6; border:1px solid #d1d5db; padding:6px 10px; border-radius:6px; cursor:pointer; font-size:13px; font-weight:500; color:#374151; transition:background 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        Collapse List ▴
                    </button>
                </div>
            </div>
            <div id="usersDirectoryContainer" style="overflow-x:auto;">
                <table id="usersDirectoryTable">
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
                        <tr class="user-row">
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

        <script>
            // Real-time Search Filter for Users
            const dirSearch = document.getElementById('userDirectorySearch');
            const dirContainer = document.getElementById('usersDirectoryContainer');
            const toggleBtn = document.getElementById('toggleDirBtn');

            dirSearch.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#usersDirectoryTable tbody tr.user-row');

                // Auto-expand when typing
                if (term.length > 0 && dirContainer.style.display === 'none') {
                    dirContainer.style.display = 'block';
                    toggleBtn.innerHTML = 'Collapse List ▴';
                }

                let visibleCount = 0;
                rows.forEach(row => {
                    const match = row.innerText.toLowerCase().includes(term);
                    row.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });

                // Show match count hint
                let hint = document.getElementById('searchHint');
                if (!hint) {
                    hint = document.createElement('div');
                    hint.id = 'searchHint';
                    hint.style.cssText = 'font-size:11px;color:#6b7280;margin-top:4px;text-align:right;padding-right:4px;';
                    dirSearch.parentNode.appendChild(hint);
                }
                hint.textContent = term ? `${visibleCount} result${visibleCount !== 1 ? 's' : ''} found` : '';
            });

            // Collapse Functionality
            function toggleUsersDirectory() {
                const container = document.getElementById('usersDirectoryContainer');
                const btn = document.getElementById('toggleDirBtn');
                
                if (container.style.display === 'none') {
                    container.style.display = 'block';
                    btn.innerHTML = 'Collapse List ▴';
                } else {
                    container.style.display = 'none';
                    btn.innerHTML = 'Expand List ▾';
                }
            }
        </script>
@endsection
