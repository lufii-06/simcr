@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="card auth-card">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h4 class="fw-bold">Login to Your Account</h4>
                <p class="text-muted">Enter your details to login</p>
            </div>
            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="form-group @error('email') has-error @enderror">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                        placeholder="name@example.com" required autofocus>
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-group @error('password') has-error @enderror">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="required">Password</label>
                    </div>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Enter password" style="padding-right: 40px;" required>
                        <i class="fas fa-eye" id="togglePassword"
                            style="cursor: pointer; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #888;"></i>
                    </div>
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">
                        Remember Me
                    </label>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-3">Login</button>
                </div>
            </form>
            <div class="text-center mt-4">
                <p class="mb-0 text-muted">Don't have an account? <a href="{{ route('register') }}"
                        class="text-primary fw-bold">Register as Client</a></p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');

            if (togglePassword && password) {
                togglePassword.addEventListener('click', function(e) {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);

                    if (type === 'text') {
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    } else {
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    }
                });
            }
        });
    </script>
@endpush
