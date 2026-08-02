@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="auth-form-wrapper">

        {{-- Header --}}
        <div class="auth-form-header">
            <div class="form-eyebrow">Welcome back</div>
            <h2>Sign in to SIMCR</h2>
            <p>Enter your credentials to access your account</p>
        </div>

        {{-- Form --}}
        <form class="auth-form" action="{{ route('login.post') }}" method="POST">
            @csrf

            {{-- Email --}}
            <div class="form-group mb-3 @error('email') has-error @enderror">
                <label for="email" class="required fw-semibold small mb-1">Email Address</label>
                <input type="email" class="form-control form-control-lg" id="email" name="email"
                    value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group mb-3 @error('password') has-error @enderror">
                <label for="password" class="required fw-semibold small mb-1">Password</label>
                <div class="input-icon-wrap">
                    <input type="password" class="form-control form-control-lg" id="password" name="password"
                        placeholder="Enter your password" required>
                    <button type="button" class="input-toggle-btn" id="togglePassword" tabindex="-1" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="auth-remember mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Remember Me</label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-auth-submit">
                Sign In
            </button>
        </form>

        {{-- Alt link --}}
        <div class="auth-alt-link mt-4">
            Don't have an account? <a href="{{ route('register') }}">Register as Client</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn  = document.querySelector('#togglePassword');
            const toggleIcon = document.querySelector('#togglePasswordIcon');
            const password   = document.querySelector('#password');

            if (toggleBtn && password) {
                toggleBtn.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);

                    if (type === 'text') {
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    } else {
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    }
                });
            }
        });
    </script>
@endpush
