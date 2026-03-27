@extends('layouts.adminlte')

@section('title', 'Update Info')

@section('content')
    <div class="card">
        <div class="card-header bg-light">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-user-edit mr-2 text-primary"></i>Update Your Information
            </h3>
        </div>

        <div class="card-body">
            <div class="d-flex align-items-center mb-4" style="gap:14px;">
                @if(!empty($user->profile_photo_path))
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                         alt="Profile"
                         style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:1px solid #e5e7eb;">
                @else
                    <div style="width:72px;height:72px;border-radius:50%;background:#f3f4f6;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-user text-muted" style="font-size:28px;"></i>
                    </div>
                @endif

                <div>
                    <div style="font-weight:800;font-size:16px;color:#111827;">{{ $user->name }}</div>
                    <div style="color:#6b7280;font-size:13px;">{{ $user->email }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('user.profile.update') }}">
                @csrf

                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">
                        Full Name
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           autocomplete="name"
                           class="form-control"
                           style="max-width:420px;"
                           required
                    />
                    @error('name')
                        <div class="text-danger" style="font-size:12px;margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">
                        Email Address
                    </label>
                    <input type="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           autocomplete="email"
                           class="form-control"
                           style="max-width:420px;"
                           required
                    />
                    @error('email')
                        <div class="text-danger" style="font-size:12px;margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">
                        Contact Number
                    </label>
                    <input type="tel"
                           name="contact_number"
                           value="{{ old('contact_number', $user->contact_number) }}"
                           placeholder="e.g. +63XXXXXXXXXX"
                           autocomplete="tel"
                           class="form-control"
                           style="max-width:420px;"
                    />
                    @error('contact_number')
                        <div class="text-danger" style="font-size:12px;margin-top:6px;">{{ $message }}</div>
                    @enderror

                    <div style="font-size:11px;color:#9ca3af;margin-top:6px;">
                        Used for security coordination (tap-to-call).
                    </div>
                </div>

                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">
                        Affiliation
                    </label>
                    <input type="text"
                           name="affiliation"
                           value="{{ old('affiliation', $user->affiliation) }}"
                           placeholder="e.g. Student, Faculty, Staff"
                           class="form-control"
                           style="max-width:420px;"
                    />
                    @error('affiliation')
                        <div class="text-danger" style="font-size:12px;margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">
                        Role
                    </label>
                    <input type="text"
                           value="{{ str_replace('_', ' ', ucfirst($user->role)) }}"
                           class="form-control"
                           style="max-width:420px;background:#f9fafb;"
                           disabled
                    />
                </div>

                <hr style="margin: 20px 0;">

                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">
                        Current Password (required to change password)
                    </label>
                    <input type="password"
                           name="current_password"
                           autocomplete="current-password"
                           class="form-control"
                           style="max-width:420px;"
                    />
                    @error('current_password')
                        <div class="text-danger" style="font-size:12px;margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">
                        New Password
                    </label>
                    <input type="password"
                           name="password"
                           autocomplete="new-password"
                           class="form-control"
                           style="max-width:420px;"
                    />
                    @error('password')
                        <div class="text-danger" style="font-size:12px;margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;">
                        Confirm New Password
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           autocomplete="new-password"
                           class="form-control"
                           style="max-width:420px;"
                    />
                </div>

                <button type="submit" class="btn btn-primary" style="padding:9px 16px;">
                    <i class="fas fa-save mr-1"></i>Update
                </button>
            </form>
        </div>
    </div>
@endsection

