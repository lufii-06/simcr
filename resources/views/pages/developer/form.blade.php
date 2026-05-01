@extends('dashboard')

@section('title', isset($developer) ? 'Edit Developer Profile' : 'Complete Developer Profile')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        {{ isset($developer) ? 'Edit Developer Profile' : 'Complete Developer Profile for ' . ($user->name ?? '') }}
                    </div>
                </div>
                <form action="{{ isset($developer) ? route('developer.update', $developer) : route('developer.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($developer))
                        @method('PUT')
                    @endif

                    @if (!isset($developer) && isset($user))
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                    @endif

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group @error('specialization_id') has-error @enderror">
                                    <label for="specialization_id" class="required">Specialization</label>
                                    <select class="form-control" data-bs-toggle="select" id="specialization_id"
                                        name="specialization_id" data-live-search="true" required>

                                        @foreach ($specializations as $spec)
                                            <option value="{{ $spec->id }}" {{ old('specialization_id', $developer->specialization_id ?? '') == $spec->id ? 'selected' : '' }}>
                                                {{ $spec->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('specialization_id')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group @error('phone') has-error @enderror">
                                    <label for="phone" class="required">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        placeholder="e.g. 08123456789" value="{{ old('phone', $developer->phone ?? '') }}"
                                        required>
                                    @error('phone')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group @error('address') has-error @enderror">
                                    <label for="address" class="required">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter your full home address" required>{{ old('address', $developer->address ?? '') }}</textarea>
                                    @error('address')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group @error('portfolio_url') has-error @enderror">
                                    <label for="portfolio_url">Portfolio / GitHub URL</label>
                                    <input type="url" class="form-control" id="portfolio_url" name="portfolio_url"
                                        placeholder="https://github.com/yourprofile"
                                        value="{{ old('portfolio_url', $developer->portfolio_url ?? '') }}">
                                    @error('portfolio_url')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button type="submit" class="btn btn-success">Save Profile</button>
                        <a href="{{ route('developer.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
