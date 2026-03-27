@extends('layouts.admin')

@section('title', 'Admin Portal Home — PSAU Parking')

@section('topbar-title', 'Admin Portal Home')

@section('topbar-right')
    <span class="badge-count">{{ $pendingRegistrations->count() }} Pending</span>
@endsection

@section('content')
    @php
        $pendingCount = $pendingRegistrations->count();
        $approvedCount = \App\Models\Registration::whereRaw('LOWER(status) = ?', ['approved'])->count();
        $violationsCount = \App\Models\Violation::count();
        $usersCount = \App\Models\User::count();
    @endphp

    <div class="card">
        <div class="card-body" style="padding: 24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;padding:10px 12px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;">
                <div style="font-size:14px;color:#111827;font-weight:700;">
                    Welcome, {{ auth()->user()->name ?? 'Administrator' }}
                </div>
                <div style="font-size:12px;color:#6b7280;" id="welcomeClock">
                    <i class="fas fa-calendar-alt"></i>
                    {{ now()->format('F d, Y') }}
                    &nbsp;|&nbsp;
                    <i class="fas fa-clock"></i>
                    {{ now()->format('h:i A') }}
                </div>
            </div>
            <div style="font-size:12px;letter-spacing:.8px;text-transform:uppercase;color:#9ca3af;font-weight:700;">
                Pampanga State Agricultural University
            </div>
            <h1 style="font-size:28px;line-height:1.2;color:#111827;margin:6px 0 2px;font-weight:800;">
                Security Unit
            </h1>
            <div style="font-size:16px;color:#6b0a16;font-weight:700;margin-bottom:10px;">
                Admin Portal
            </div>
            <p style="font-size:14px;color:#4b5563;max-width:880px;line-height:1.6;">
                This portal helps administrators manage vehicle registration workflows, monitor approved sticker releases, enforce violations and sanctions, and maintain secure user accounts across the PSAU parking system.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-th-large" style="color:#6b0a16;"></i>
                Admin Navigation
            </div>
        </div>
        <div style="padding:20px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;">
                <a href="{{ route('admin.dashboard') }}" style="text-decoration:none;">
                    <div class="stat-card c-maroon" style="min-height:150px;align-items:flex-start;">
                        <div class="stat-icon c-maroon"><i class="fas fa-clipboard-list"></i></div>
                        <div>
                            <div style="font-size:16px;font-weight:800;color:#111827;">Pending Reviews</div>
                            <div style="font-size:13px;color:#6b7280;margin-top:6px;line-height:1.5;">
                                Review submitted vehicle applications, verify uploaded documents, then approve or reject requests.
                            </div>
                            <div style="font-size:12px;color:#6b0a16;font-weight:700;margin-top:8px;">{{ $pendingCount }} awaiting review</div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.approved.index') }}" style="text-decoration:none;">
                    <div class="stat-card c-green" style="min-height:150px;align-items:flex-start;">
                        <div class="stat-icon c-green"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div style="font-size:16px;font-weight:800;color:#111827;">Approved</div>
                            <div style="font-size:13px;color:#6b7280;margin-top:6px;line-height:1.5;">
                                Manage approved registrations, print QR stickers, and schedule or reschedule claim dates.
                            </div>
                            <div style="font-size:12px;color:#166534;font-weight:700;margin-top:8px;">{{ $approvedCount }} approved records</div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.sanctions.index') }}" style="text-decoration:none;">
                    <div class="stat-card c-red" style="min-height:150px;align-items:flex-start;">
                        <div class="stat-icon c-red"><i class="fas fa-balance-scale"></i></div>
                        <div>
                            <div style="font-size:16px;font-weight:800;color:#111827;">Violations & Sanctions</div>
                            <div style="font-size:13px;color:#6b7280;margin-top:6px;line-height:1.5;">
                                Track violation incidents, assign sanctions, and monitor active or resolved enforcement actions.
                            </div>
                            <div style="font-size:12px;color:#991b1b;font-weight:700;margin-top:8px;">{{ $violationsCount }} logged violations</div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.users.index') }}" style="text-decoration:none;">
                    <div class="stat-card c-blue" style="min-height:150px;align-items:flex-start;">
                        <div class="stat-icon c-blue"><i class="fas fa-users-cog"></i></div>
                        <div>
                            <div style="font-size:16px;font-weight:800;color:#111827;">User Management</div>
                            <div style="font-size:13px;color:#6b7280;margin-top:6px;line-height:1.5;">
                                Create and manage admin, security, and user accounts while enforcing role-based access.
                            </div>
                            <div style="font-size:12px;color:#1d4ed8;font-weight:700;margin-top:8px;">{{ $usersCount }} active accounts</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-cogs" style="color:#6b0a16;"></i>
                Admin Portal Functions
            </div>
        </div>
        <div style="padding: 22px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;">
                <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff;">
                    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:6px;">Registration Validation</div>
                    <div style="font-size:13px;color:#6b7280;line-height:1.55;">Checks and verifies user-submitted vehicle records and supporting documents for approval compliance.</div>
                </div>
                <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff;">
                    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:6px;">Sticker Issuance Workflow</div>
                    <div style="font-size:13px;color:#6b7280;line-height:1.55;">Handles QR sticker generation, bulk printing, and pick-up scheduling based on business-day rules.</div>
                </div>
                <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff;">
                    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:6px;">Security Enforcement Support</div>
                    <div style="font-size:13px;color:#6b7280;line-height:1.55;">Provides an audit-ready violation and sanction module to enforce parking and security policies.</div>
                </div>
                <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff;">
                    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:6px;">Account Governance</div>
                    <div style="font-size:13px;color:#6b7280;line-height:1.55;">Manages role-based access and secure account lifecycle for administrators, security officers, and users.</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function initWelcomeClock() {
    const clockEl = document.getElementById('welcomeClock');
    if (!clockEl) return;

    function fmt(now) {
        const months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        const month = months[now.getMonth()];
        const day = String(now.getDate()).padStart(2, '0');
        const year = now.getFullYear();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        const hh = String(hours).padStart(2, '0');
        return `${month} ${day}, ${year} | ${hh}:${minutes} ${ampm}`;
    }

    function render() {
        clockEl.innerHTML =
            '<i class="fas fa-calendar-alt"></i> ' +
            fmt(new Date()).replace(' | ', ' &nbsp;|&nbsp; <i class="fas fa-clock"></i> ');
    }

    render();
    setInterval(render, 1000);
})();
</script>
@endpush
