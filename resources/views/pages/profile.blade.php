@extends('dashboard')

@section('title', 'My Profile')

@section('content')
    <div class="page-inner">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Edit Profile Information</div>
                    </div>
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <div class="avatar-xxl mb-3 mx-auto">
                                        <img src="{{ $user->getAvatarUrl() }}" alt="profile" id="preview-avatar"
                                            class="avatar-img rounded-circle border border-4 border-white shadow">
                                    </div>
                                    <div class="form-group p-0">
                                        <label for="avatar" class="btn  btn-sm btn-round">
                                            <i class="fa fa-camera me-1"></i> Change Photo
                                        </label>
                                        <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*"
                                            onchange="previewImage(this)">
                                        @error('avatar')
                                            <br><small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group @error('name') has-error @enderror">
                                        <label for="name" class="required">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Enter your full name"
                                            value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="form-group @error('email') has-error @enderror">
                                        <label for="email" class="required">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="example@mail.com"
                                            value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Role</label>
                                        <input type="text" class="form-control" value="{{ strtoupper($user->role) }}"
                                            disabled>
                                        <small class="text-muted">Role cannot be changed by user.</small>
                                    </div>

                                    <div class="separator-dashed"></div>

                                    @if (($user->role ?? '') == 'client')
                                        <div class="form-group @error('company_name') has-error @enderror">
                                            <label for="company_name" class="required">Company Name</label>
                                            <input type="text" class="form-control" id="company_name" name="company_name"
                                                placeholder="Enter company name"
                                                value="{{ old('company_name', $user->client->company_name ?? '') }}"
                                                required>
                                            @error('company_name')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group @error('phone') has-error @enderror">
                                                    <label for="phone" class="required">Company Phone</label>
                                                    <input type="text" class="form-control" id="phone" name="phone"
                                                        placeholder="e.g. 0812345678"
                                                        value="{{ old('phone', $user->client->phone ?? '') }}" required>
                                                    @error('phone')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group @error('address') has-error @enderror">
                                            <label for="address" class="required">Company Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter full company address" required>{{ old('address', $user->client->address ?? '') }}</textarea>
                                            @error('address')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    @elseif (($user->role ?? '') == 'developer')
                                        <div class="form-group @error('specialization_id') has-error @enderror">
                                            <label for="specialization_id" class="required">Specialization</label>
                                            <select class="form-control" data-bs-toggle="select" id="specialization_id"
                                                name="specialization_id" data-live-search="true" required>

                                                @foreach ($specializations ?? [] as $spec)
                                                    <option value="{{ $spec->id }}"
                                                        {{ old('specialization_id', $user->developer->specialization_id ?? '') == $spec->id ? 'selected' : '' }}>
                                                        {{ $spec->name ?? 'Unknown' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('specialization_id')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group @error('phone') has-error @enderror">
                                                    <label for="phone" class="required">Phone Number</label>
                                                    <input type="text" class="form-control" id="phone" name="phone"
                                                        placeholder="e.g. 0812345678"
                                                        value="{{ old('phone', $user->developer->phone ?? '') }}" required>
                                                    @error('phone')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group @error('portfolio_url') has-error @enderror">
                                                    <label for="portfolio_url">Portfolio URL</label>
                                                    <input type="url" class="form-control" id="portfolio_url"
                                                        name="portfolio_url"
                                                        placeholder="https://github.com/yourprofile"
                                                        value="{{ old('portfolio_url', $user->developer->portfolio_url ?? '') }}">
                                                    @error('portfolio_url')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group @error('address') has-error @enderror">
                                            <label for="address" class="required">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter your full address" required>{{ old('address', $user->developer->address ?? '') }}</textarea>
                                            @error('address')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-success">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Update Password</div>
                    </div>
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group @error('current_password') has-error @enderror">
                                <label for="current_password" class="required">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password"
                                    placeholder="Enter current password" required>
                                @error('current_password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group @error('password') has-error @enderror">
                                <label for="password" class="required">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                    placeholder="Min 8 characters" required>
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation" class="required">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" placeholder="Repeat new password" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary w-100">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview-avatar').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
    @endpush
@endsection
