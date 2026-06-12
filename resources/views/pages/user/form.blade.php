@extends('dashboard')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
    <style>
        .avatar-upload-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }

        .avatar-upload-container {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            cursor: pointer;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            border: 3px solid #fff;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .avatar-upload-container:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            border-color: #3182ce;
        }
        
        .avatar-upload-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: #fff;
        }
        
        .avatar-upload-container:hover .avatar-upload-overlay {
            opacity: 1;
        }
        
        .avatar-upload-overlay i {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .avatar-upload-overlay span {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .btn-upload-trigger {
            font-weight: 500;
            font-size: 13px !important;
            padding: 5px 15px !important;
        }
    </style>

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
                                    <label class="d-block mb-2">Profile Picture</label>
                                    <div class="input-file input-file-image d-inline-block">
                                        <div class="avatar-upload-wrapper">
                                            <!-- Clickable Circular Avatar Preview -->
                                            <div class="avatar-upload-container" onclick="document.getElementById('avatar').click()">
                                                <img class="avatar-upload-preview img-upload-preview"
                                                    src="{{ isset($user) ? $user->getAvatarUrl() : asset('images/user-default.png') }}"
                                                    alt="preview">
                                                <div class="avatar-upload-overlay">
                                                    <i class="fas fa-camera"></i>
                                                    <span>Ganti Foto</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Hidden Input File -->
                                        <input type="file" class="form-control" id="avatar"
                                            name="avatar" accept="image/*" style="display: none;">
                                            
                                        <!-- Action Button below image -->
                                        <div class="mt-1">
                                            <button type="button" class="btn btn-outline-primary btn-sm btn-round btn-upload-trigger" onclick="document.getElementById('avatar').click()">
                                                <i class="fas fa-image me-1"></i> Pilih Gambar
                                            </button>
                                        </div>
                                        
                                        @error('avatar')
                                            <small class="form-text text-danger d-block mt-2">{{ $message }}</small>
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
                                        <option value="client"
                                            {{ old('role', $user->role ?? '') == 'client' ? 'selected' : '' }}>Client
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
