@extends('layouts.app')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body p-4">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.change-password.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password / موجودہ پاس ورڈ</label>
                        <input type="password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               placeholder="Enter current password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password / نیا پاس ورڈ</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter new password (min 8 chars)" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm Password / پاس ورڈ کی تصدیق</label>
                        <input type="password" name="password_confirmation"
                               class="form-control"
                               placeholder="Re-enter new password" required>
                    </div>

                    <button type="submit" class="btn btn-green w-100 py-2">
                        <i class="bi bi-check-lg me-2"></i>Update Password
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
