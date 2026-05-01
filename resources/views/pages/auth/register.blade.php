@extends('layouts\auth')

@section('title', 'Register as Client')

@section('content')
    <div class="card auth-card register-card">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h4 class="fw-bold">Create Client Account</h4>
                <p class="text-muted">Fill in the details to register your company</p>
            </div>
            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-user-circle me-2"></i> Account Information
                        </h5>
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name" class="required">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name') }}" placeholder="Enter your full name" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group @error('email') has-error @enderror">
                            <label for="email" class="required">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email') }}" placeholder="name@example.com" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group @error('password') has-error @enderror">
                            <label for="password" class="required">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Create a strong password" required>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation" class="required">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" placeholder="Repeat your password" required>
                        </div>
                    </div>
                    <div class="col-md-6 border-start">
                        <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-building me-2"></i> Company Profile</h5>
                        <div class="form-group @error('company_name') has-error @enderror">
                            <label for="company_name" class="required">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name"
                                value="{{ old('company_name') }}" placeholder="Enter company name" required>
                            @error('company_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group @error('main_contact') has-error @enderror">
                            <label for="main_contact" class="required">Main Contact Person</label>
                            <input type="text" class="form-control" id="main_contact" name="main_contact"
                                value="{{ old('main_contact') }}" placeholder="e.g. CEO, Manager" required>
                            @error('main_contact')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group @error('phone') has-error @enderror">
                            <label for="phone" class="required">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="{{ old('phone') }}" placeholder="e.g. 0812345678" required>
                            @error('phone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group @error('address') has-error @enderror">
                            <label for="address" class="required">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="1" placeholder="Enter company full address" required>{{ old('address') }}</textarea>
                            @error('address')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-3">Register Now</button>
                </div>
            </form>
            <div class="text-center mt-4">
                <p class="mb-0 text-muted">Already have an account? <a href="{{ route('login') }}"
                        class="text-primary fw-bold">Login here</a></p>
            </div>
        </div>
    </div>
@endsection
