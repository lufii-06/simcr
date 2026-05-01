@extends('dashboard')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ isset($user) ? 'Edit User Form' : 'Create User Form' }}</div>
                </div>
                <form action="{{ isset($user) ? route('user.update', $user) : route('user.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @if (isset($user))
                        @method('PUT')
                    @endif
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label>Profile Picture</label>
                                    <div class="input-file input-file-image">
                                        <img class="img-upload-preview mb-3 img-thumbnail" width="150" height="150"
                                            src="{{ isset($user) ? $user->getAvatarUrl() : asset('images/user-default.png') }}"
                                            alt="preview">
                                        <input type="file" class="form-control form-control-file" id="avatar"
                                            name="avatar" accept="image/*">
                                        @error('avatar')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group @error('name') has-error @enderror">
                                    <label for="name" class="required">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter Full Name" value="{{ old('name', $user->name ?? '') }}" required>
                                    @error('name')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group @error('email') has-error @enderror">
                                    <label for="email" class="required">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter Email (e.g. user@simcr.com)" value="{{ old('email', $user->email ?? '') }}" required>
                                    @error('email')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group @error('role') has-error @enderror">
                                    <label for="role" class="required">Role</label>
                                    <select class="form-control" data-bs-toggle="select" data-btn-width="100%"
                                        data-live-search="true" id="role" name="role" required>
                                        <option value="leader"
                                            {{ old('role', $user->role ?? '') == 'leader' ? 'selected' : '' }}>Leader
                                        </option>
                                        <option value="client"
                                            {{ old('role', $user->role ?? '') == 'client' ? 'selected' : '' }}>Client
                                        </option>
                                        <option value="pm"
                                            {{ old('role', $user->role ?? '') == 'pm' ? 'selected' : '' }}>Project Manager
                                        </option>
                                        <option value="developer"
                                            {{ old('role', $user->role ?? '') == 'developer' ? 'selected' : '' }}>Developer
                                        </option>
                                    </select>
                                    @error('role')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                @if (!isset($user))
                                    <div class="form-group @error('password') has-error @enderror">
                                        <label for="password" class="required">Password</label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Enter Password (Min 8 characters)" required>
                                        @error('password')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('user.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#avatar').change(function() {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        $('.img-upload-preview').attr('src', event.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endpush
