@extends('layouts.adminlte')

@section('title', 'Approved Registrations')

@section('content')
<div class="row">
    <div class="col-12">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            </div>
        @endif

        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-circle mr-2 text-success"></i>Approved Vehicle Registrations</h3>
                <div class="card-tools">
                    <span class="badge badge-success badge-lg">{{ $approvedRegistrations->total() }} Records</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($approvedRegistrations->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>No approved registrations yet.</h5>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped border mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Registrant</th>
                                    <th>Vehicle</th>
                                    <th>QR Sticker ID</th>
                                    <th>Pick-up Schedule</th>
                                    <th style="width:200px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvedRegistrations as $reg)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $reg->user->name }}</div>
                                            <div class="text-muted small">{{ $reg->user->email }}</div>
                                            <div class="text-muted small">Approved: {{ $reg->approved_at?->format('M d, Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold">{{ $reg->vehicle->make }} {{ $reg->vehicle->model }}</div>
                                            <div class="text-muted small">{{ $reg->vehicle->color }}</div>
                                            <span class="badge badge-info text-uppercase">{{ $reg->vehicle->plate_number }}</span>
                                        </td>
                                        <td>
                                            @if($reg->qr_sticker_id)
                                                <span class="badge badge-secondary d-block mb-1" style="font-family:monospace;font-size:12px">{{ $reg->qr_sticker_id }}</span>
                                                <a href="{{ route('admin.approved.qr', $reg->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-print mr-1"></i> Print QR
                                                </a>
                                            @else
                                                <span class="text-muted small font-italic">No QR assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($reg->pickupSchedule)
                                                @if($reg->pickupSchedule->is_completed)
                                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Claimed</span>
                                                    <div class="text-muted small mt-1">{{ $reg->pickupSchedule->completed_at?->format('M d, Y g:i A') }}</div>
                                                @else
                                                    <span class="badge badge-warning text-dark"><i class="fas fa-calendar mr-1"></i>Scheduled</span>
                                                    <div class="font-weight-bold small mt-1">{{ $reg->pickupSchedule->pickup_date->format('M d, Y') }}</div>
                                                    <div class="text-muted small">{{ date('g:i A', strtotime($reg->pickupSchedule->pickup_time)) }} · {{ $reg->pickupSchedule->location }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted small font-italic">Not scheduled</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary btn-block mb-1"
                                                data-toggle="collapse" data-target="#schedule-{{ $reg->id }}" aria-expanded="false">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                {{ $reg->pickupSchedule ? 'Reschedule' : 'Schedule Pick-up' }}
                                            </button>
                                            @if($reg->pickupSchedule && !$reg->pickupSchedule->is_completed)
                                                <form method="POST" action="{{ route('admin.approved.claim', $reg->id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success btn-block"
                                                        onclick="return confirm('Mark sticker as claimed?')">
                                                        <i class="fas fa-check mr-1"></i> Mark Claimed
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    {{-- Collapsible schedule form row --}}
                                    <tr class="collapse" id="schedule-{{ $reg->id }}">
                                        <td colspan="5" class="bg-light border-top p-0">
                                            <div class="p-3">
                                                <form method="POST" action="{{ route('admin.approved.schedule', $reg->id) }}" class="form-inline flex-wrap" style="gap:10px">
                                                    @csrf
                                                    <div class="form-group mr-3 mb-2">
                                                        <label class="mr-2 font-weight-bold small">Date:</label>
                                                        <input type="date" name="pickup_date" class="form-control form-control-sm" required
                                                            min="{{ date('Y-m-d') }}"
                                                            value="{{ $reg->pickupSchedule?->pickup_date?->format('Y-m-d') }}">
                                                    </div>
                                                    <div class="form-group mr-3 mb-2">
                                                        <label class="mr-2 font-weight-bold small">Time:</label>
                                                        <input type="time" name="pickup_time" class="form-control form-control-sm" required
                                                            value="{{ $reg->pickupSchedule?->pickup_time }}">
                                                    </div>
                                                    <div class="form-group mr-3 mb-2">
                                                        <label class="mr-2 font-weight-bold small">Location:</label>
                                                        <input type="text" name="location" class="form-control form-control-sm" required
                                                            placeholder="e.g. Admin Office, Gate 1" style="width:200px"
                                                            value="{{ $reg->pickupSchedule?->location }}">
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-primary mb-2">
                                                        <i class="fas fa-save mr-1"></i> Save Schedule
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $approvedRegistrations->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
