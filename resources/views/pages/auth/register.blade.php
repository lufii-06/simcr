@extends('layouts\auth')

@section('title', 'Register as Client')

@section('content')
    <div class="auth-form-wrapper register-wrapper">

        {{-- Header --}}
        <div class="auth-form-header">
            <div class="form-eyebrow">Get started</div>
            <h2>Create Client Account</h2>
            <p>Fill in your account and company details to get started</p>
        </div>

        {{-- Form --}}
        <form class="auth-form flex-grow-1" action="{{ route('register.post') }}" method="POST">
            @csrf

            <div class="row g-4">

                {{-- ---- LEFT COLUMN: Account Info ---- --}}
                <div class="col-12 col-md-6">
                    <div class="auth-section-title">
                        <i class="fas fa-user-circle"></i>
                        Account Information
                    </div>

                    {{-- Full Name --}}
                    <div class="form-group mb-3 @error('name') has-error @enderror">
                        <label for="name" class="required fw-semibold small mb-1">Full Name</label>
                        <input type="text" class="form-control form-control-lg" id="name" name="name"
                            value="{{ old('name') }}" placeholder="Enter your full name" required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="form-group mb-3 @error('email') has-error @enderror">
                        <label for="email" class="required fw-semibold small mb-1">Email Address</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email"
                            value="{{ old('email') }}" placeholder="name@example.com" required>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group mb-3 @error('password') has-error @enderror">
                        <label for="password" class="required fw-semibold small mb-1">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password"
                            placeholder="Create a strong password" required>
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="form-group mb-3">
                        <label for="password_confirmation" class="required fw-semibold small mb-1">Confirm Password</label>
                        <input type="password" class="form-control form-control-lg" id="password_confirmation"
                            name="password_confirmation" placeholder="Repeat your password" required>
                    </div>
                </div>

                {{-- ---- RIGHT COLUMN: Company Profile ---- --}}
                <div class="col-12 col-md-6">
                    <div class="auth-section-title">
                        <i class="fas fa-building"></i>
                        Company Profile
                    </div>

                    {{-- Company Name --}}
                    <div class="form-group mb-3 @error('company_name') has-error @enderror">
                        <label for="company_name" class="required fw-semibold small mb-1">Company Name</label>
                        <input type="text" class="form-control form-control-lg" id="company_name" name="company_name"
                            value="{{ old('company_name') }}" placeholder="Enter company name" required>
                        @error('company_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Main Contact --}}
                    <div class="form-group mb-3 @error('main_contact') has-error @enderror">
                        <label for="main_contact" class="required fw-semibold small mb-1">Main Contact Person</label>
                        <input type="text" class="form-control form-control-lg" id="main_contact" name="main_contact"
                            value="{{ old('main_contact') }}" placeholder="e.g. CEO, Manager" required>
                        @error('main_contact')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="form-group mb-3 @error('phone') has-error @enderror">
                        <label for="phone" class="required fw-semibold small mb-1">Phone Number</label>
                        <input type="text" class="form-control form-control-lg" id="phone" name="phone"
                            value="{{ old('phone') }}" placeholder="e.g. 0812345678" required>
                        @error('phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Address --}}
                    <div class="form-group mb-3 @error('address') has-error @enderror">
                        <label for="address" class="required fw-semibold small mb-1">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"
                            placeholder="Enter company full address" required>{{ old('address') }}</textarea>
                        @error('address')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="mt-4">
                <button type="submit" class="btn-auth-submit">
                    Create Account
                </button>
            </div>
        </form>

        {{-- Alt link --}}
        <div class="auth-alt-link mt-3">
            Already have an account? <a href="{{ route('login') }}">Sign in here</a>
        </div>
    </div>
@endsection
