@extends('layouts.admin')

@section('title', 'Approved Registrations — PSAU Parking')

@section('topbar-title', 'Approved Vehicle Registrations')

@section('content')
        <div class="tab-nav">
            <a class="tab-btn" href="{{ route('admin.dashboard') }}">⏳ Pending Reviews</a>
            <span class="tab-btn active tab-approved">✅ Approved ({{ $approvedRegistrations->total() }})</span>
            <a class="tab-btn" href="{{ route('admin.sanctions.index') }}">⚖️ Violations & Sanctions</a>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <svg width="18" height="18" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    Approved Registrations Directory
                </div>
                <form id="bulkPrintForm" method="POST" action="{{ route('admin.approved.qr.bulk') }}" style="display:flex;align-items:center;gap:8px;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" id="bulkPrintBtn" disabled>
                        🖨 Print Selected
                    </button>
                    <span style="font-size:11px;color:#6b7280;" id="bulkSelectedLabel">0 selected</span>
                </form>
            </div>

            @if($approvedRegistrations->isEmpty())
                <div class="empty-state">
                    <div style="font-size:40px;margin-bottom:12px">✅</div>
                    <p style="font-size:15px;font-weight:600;color:#6b7280;">No approved registrations yet.</p>
                </div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:42px;text-align:center;">
                                    <input type="checkbox" id="selectAllPrintables" title="Select all printable">
                                </th>
                                <th>Registrant</th>
                                <th>Vehicle / Plate</th>
                                <th>Sticker & QR</th>
                                <th>Pick-up Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvedRegistrations as $reg)
                                <tr>
                                    <td style="text-align:center;">
                                        @if($reg->qr_sticker_id)
                                            <input type="checkbox"
                                                   class="bulk-print-checkbox"
                                                   name="registration_ids[]"
                                                   value="{{ $reg->id }}"
                                                   form="bulkPrintForm">
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#111827">{{ $reg->user->name }}</div>
                                        <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $reg->user->email }}</div>
                                        <div style="font-size:11px;color:#6b7280;margin-top:2px">Approved: {{ $reg->approved_at?->format('M d, Y') }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#111827">{{ $reg->vehicle->make }} {{ $reg->vehicle->model }}</div>
                                        <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $reg->vehicle->color }}</div>
                                        <div style="margin-top:4px"><span class="plate-tag">{{ $reg->vehicle->plate_number }}</span></div>
                                    </td>
                                    <td>
                                        @if($reg->qr_sticker_id)
                                            <span class="badge badge-secondary" style="margin-bottom:6px">{{ $reg->qr_sticker_id }}</span><br>
                                            <a href="{{ route('admin.approved.qr', $reg->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> 
                                                Print QR
                                            </a>
                                            <div style="margin-top:6px;font-size:11px;color:#4b5563;line-height:1.35">
                                                @if(($reg->qr_print_count ?? 0) > 0)
                                                    <span style="color:#166534;font-weight:600;">Printed {{ $reg->qr_print_count }} time{{ $reg->qr_print_count > 1 ? 's' : '' }}</span>
                                                    @if($reg->last_qr_printed_at)
                                                        <div style="color:#6b7280;">Last print: {{ $reg->last_qr_printed_at->format('M d, Y g:i A') }}</div>
                                                    @endif
                                                @else
                                                    <span style="color:#9a3412;">Not printed yet</span>
                                                @endif
                                            </div>
                                        @else
                                            <span style="font-size:11px;color:#9ca3af;font-style:italic">No QR assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reg->pickupSchedule)
                                            @if($reg->pickupSchedule->is_completed)
                                                <span class="badge badge-active" style="margin-bottom:4px">Claimed</span>
                                                <div style="font-size:11px;color:#6b7280">{{ $reg->pickupSchedule->completed_at?->format('M d, Y g:i A') }}</div>
                                            @else
                                                <span class="badge badge-warning" style="margin-bottom:4px;color:#854d0e;">Scheduled</span>
                                                <div style="font-weight:600;font-size:12px;color:#111827">{{ $reg->pickupSchedule->pickup_date->format('M d, Y') }}</div>
                                                <div style="font-size:11px;color:#6b7280">{{ date('g:i A', strtotime($reg->pickupSchedule->pickup_time)) }} · {{ $reg->pickupSchedule->location }}</div>
                                            @endif
                                        @else
                                            <span style="font-size:11px;color:#9ca3af;font-style:italic">Not scheduled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex;flex-direction:column;gap:6px">
                                            <button class="btn btn-outline-primary btn-sm" onclick="toggleScheduleForm({{ $reg->id }})">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $reg->pickupSchedule ? 'Reschedule' : 'Schedule Pick-up' }}
                                            </button>
                                            <form method="POST" action="{{ route('admin.approved.schedule', $reg->id) }}">
                                                @csrf
                                                <input type="hidden" name="auto_schedule_option" value="day_after_tomorrow">
                                                <input type="hidden" name="location" value="{{ $reg->pickupSchedule?->location ?? 'Security Office' }}">
                                                <button type="submit" class="btn btn-gray btn-sm" style="width:100%">
                                                    Auto: Day After Tomorrow
                                                </button>
                                                <div style="font-size:10px;color:#6b7280;margin-top:2px;">
                                                    {{ $autoSuggestions['day_after_tomorrow']->format('M d, Y (D) · g:i A') }}
                                                </div>
                                            </form>
                                            <form method="POST" action="{{ route('admin.approved.schedule', $reg->id) }}">
                                                @csrf
                                                <input type="hidden" name="auto_schedule_option" value="next_week">
                                                <input type="hidden" name="location" value="{{ $reg->pickupSchedule?->location ?? 'Security Office' }}">
                                                <button type="submit" class="btn btn-gray btn-sm" style="width:100%">
                                                    Auto: Next Week
                                                </button>
                                                <div style="font-size:10px;color:#6b7280;margin-top:2px;">
                                                    {{ $autoSuggestions['next_week']->format('M d, Y (D) · g:i A') }}
                                                </div>
                                            </form>
                                            @if($reg->pickupSchedule && !$reg->pickupSchedule->is_completed)
                                                <form method="POST" action="{{ route('admin.approved.claim', $reg->id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" style="width:100%" onclick="return confirm('Mark sticker as claimed?')">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Claim
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                {{-- Inline schedule form --}}
                                <tr id="schedule-row-{{ $reg->id }}" style="display:none;background:#f8fafc;border-top:1px solid #e2e8f0;">
                                    <td colspan="6" style="padding:16px 20px;">
                                        <div style="font-size:12px;color:#1e293b;font-weight:600;margin-bottom:10px">Schedule Pick-up for {{ $reg->user->name }}</div>
                                        <form method="POST" action="{{ route('admin.approved.schedule', $reg->id) }}" class="form-inline">
                                            @csrf
                                            <div class="form-group">
                                                <label style="font-size:11px;font-weight:600;color:#374151">Date:</label>
                                                <input type="date" name="pickup_date" class="form-control form-control-sm" required min="{{ date('Y-m-d') }}" value="{{ $reg->pickupSchedule?->pickup_date?->format('Y-m-d') }}">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-size:11px;font-weight:600;color:#374151">Time:</label>
                                                <input type="time" name="pickup_time" class="form-control form-control-sm" required value="{{ $reg->pickupSchedule?->pickup_time }}">
                                            </div>
                                            <div class="form-group">
                                                <label style="font-size:11px;font-weight:600;color:#374151">Location:</label>
                                                <input type="text" name="location" class="form-control form-control-sm" placeholder="e.g. Admin Office" required style="width:180px" value="{{ $reg->pickupSchedule?->location }}">
                                            </div>
                                            <div class="form-group" style="margin-left:auto">
                                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                <button type="button" class="btn btn-gray btn-sm" onclick="toggleScheduleForm({{ $reg->id }})">Cancel</button>
                                            </div>
                                            <div style="width:100%;font-size:11px;color:#6b7280;margin-top:2px;">
                                                Business window: Monday to Thursday, 8:00 AM to 4:00 PM.
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:16px 22px;border-top:1px solid #f3f4f6">{{ $approvedRegistrations->links() }}</div>
            @endif
        </div>
@endsection

@push('scripts')
<script>
function toggleScheduleForm(id) {
    const el = document.getElementById('schedule-row-' + id);
    el.style.display = el.style.display === 'table-row' ? 'none' : 'table-row';
}

(function initBulkPrintSelection() {
    const selectAll = document.getElementById('selectAllPrintables');
    const checkboxes = Array.from(document.querySelectorAll('.bulk-print-checkbox'));
    const bulkBtn = document.getElementById('bulkPrintBtn');
    const label = document.getElementById('bulkSelectedLabel');

    if (!selectAll || !bulkBtn || !label) return;

    function updateState() {
        const selectedCount = checkboxes.filter(cb => cb.checked).length;
        bulkBtn.disabled = selectedCount === 0;
        label.textContent = selectedCount + ' selected';

        if (checkboxes.length === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
            return;
        }

        selectAll.checked = selectedCount === checkboxes.length;
        selectAll.indeterminate = selectedCount > 0 && selectedCount < checkboxes.length;
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
        updateState();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateState));
    updateState();
})();
</script>
@endpush
