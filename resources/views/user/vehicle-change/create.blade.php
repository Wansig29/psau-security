@extends('layouts.adminlte')

@section('title', 'Request Vehicle Change')

@section('content')
<div class="container-fluid" style="max-width:760px;margin:0 auto;">

    <div class="card shadow-sm" style="border-radius:12px;overflow:hidden;border:none;">
        <div style="background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 100%);padding:28px 28px 22px;">
            <h2 style="color:#fff;font-weight:800;margin:0;font-size:1.4rem;">
                <i class="fas fa-exchange-alt mr-2"></i> Request Vehicle Change
            </h2>
            <p style="color:rgba(255,255,255,0.8);margin:6px 0 0;font-size:.92rem;">
                Submit new vehicle documents for admin review. Your current sticker will be revoked once approved.
            </p>
        </div>

        {{-- Current vehicle info banner --}}
        <div style="background:#eff6ff;border-bottom:1px solid #dbeafe;padding:14px 28px;">
            <div style="font-size:12px;color:#1e40af;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Current Registered Vehicle</div>
            <div style="font-weight:800;font-size:1.05rem;color:#1e3a8a;font-family:monospace;">
                {{ $activeRegistration->vehicle->plate_number }}
            </div>
            <div style="font-size:.88rem;color:#3b82f6;margin-top:2px;">
                {{ $activeRegistration->vehicle->make }} {{ $activeRegistration->vehicle->model }} · {{ $activeRegistration->vehicle->color }}
            </div>
        </div>

        <div class="card-body" style="padding:28px;">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('user.vehicle-change.store') }}" enctype="multipart/form-data">
                @csrf

                <h5 style="font-weight:700;color:#1e3a8a;border-bottom:2px solid #dbeafe;padding-bottom:8px;margin-bottom:18px;">
                    <i class="fas fa-car mr-2"></i> New Vehicle Information
                </h5>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Make <span class="text-danger">*</span></label>
                            <input type="text" name="new_make" class="form-control @error('new_make') is-invalid @enderror"
                                   value="{{ old('new_make') }}" placeholder="e.g. Toyota" required>
                            @error('new_make')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Model <span class="text-danger">*</span></label>
                            <input type="text" name="new_model" class="form-control @error('new_model') is-invalid @enderror"
                                   value="{{ old('new_model') }}" placeholder="e.g. Vios" required>
                            @error('new_model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Color <span class="text-danger">*</span></label>
                            <input type="text" name="new_color" class="form-control @error('new_color') is-invalid @enderror"
                                   value="{{ old('new_color') }}" placeholder="e.g. White" required>
                            @error('new_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Reason for Change <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                              rows="3" placeholder="Briefly explain why you need to change your registered vehicle…" required>{{ old('reason') }}</textarea>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <h5 style="font-weight:700;color:#1e3a8a;border-bottom:2px solid #dbeafe;padding-bottom:8px;margin:24px 0 18px;">
                    <i class="fas fa-file-image mr-2"></i> Supporting Documents
                </h5>
                <p class="text-muted small mb-3">Upload clear photos of all required documents for the <strong>new vehicle</strong>.</p>

                @php
                    $docFields = [
                        'doc_vehicle_photo' => 'Vehicle Photo',
                        'doc_or'            => 'Official Receipt (OR)',
                        'doc_cr'            => 'Certificate of Registration (CR)',
                        'doc_cor'           => 'Certificate of Registration (COR)',
                        'doc_license'       => "Driver's License",
                        'doc_school_id'     => 'School ID',
                    ];
                @endphp

                <div class="row">
                    @foreach($docFields as $field => $label)
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold" style="font-size:.9rem;">
                            {{ $label }} <span class="text-danger">*</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error($field) is-invalid @enderror"
                                   id="{{ $field }}" name="{{ $field }}"
                                   accept="image/jpeg,image/png,image/jpg,image/heic,image/heif" required>
                            <label class="custom-file-label" for="{{ $field }}">Choose file…</label>
                            @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    @endforeach
                </div>

                <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:14px 18px;margin-top:8px;">
                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                    <strong>Important:</strong> Once your request is approved, your current vehicle registration and parking sticker will be permanently revoked. You will receive a new sticker for the replacement vehicle.
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('user.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg font-weight-bold"
                            style="background:#1d4ed8;border-color:#1d4ed8;padding:10px 30px;">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Change Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Show chosen filename in custom file inputs
document.querySelectorAll('.custom-file-input').forEach(function(input) {
    input.addEventListener('change', function() {
        const label = this.nextElementSibling;
        if (this.files && this.files[0]) {
            label.textContent = this.files[0].name;
        }
    });
});
</script>
@endsection
