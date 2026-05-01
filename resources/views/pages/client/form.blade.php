@extends('dashboard')

@section('title', isset($client) ? 'Edit Client Profile' : 'Complete Client Profile')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        {{ isset($client) ? 'Edit Client Profile' : 'Complete Client Profile for ' . ($user->name ?? '') }}
                    </div>
                </div>
                <form action="{{ isset($client) ? route('client.update', $client) : route('client.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($client))
                        @method('PUT')
                    @endif

                    @if (!isset($client) && isset($user))
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                    @endif

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group @error('company_name') has-error @enderror">
                                    <label for="company_name" class="required">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name"
                                        placeholder="Enter Company Name (e.g. PT. Maju Jaya)"
                                        value="{{ old('company_name', $client->company_name ?? '') }}" required>
                                    @error('company_name')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group @error('main_contact') has-error @enderror">
                                    <label for="main_contact" class="required">Main Contact Person</label>
                                    <input type="text" class="form-control" id="main_contact" name="main_contact"
                                        placeholder="Enter Contact Name (e.g. John Doe)"
                                        value="{{ old('main_contact', $client->main_contact ?? '') }}" required>
                                    @error('main_contact')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group @error('phone') has-error @enderror">
                                    <label for="phone" class="required">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        placeholder="e.g. 08123456789" value="{{ old('phone', $client->phone ?? '') }}"
                                        required>
                                    @error('phone')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group @error('address') has-error @enderror">
                                    <label for="address" class="required">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter Company Full Address"
                                        required>{{ old('address', $client->address ?? '') }}</textarea>
                                    @error('address')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button type="submit" class="btn btn-success">Save Profile</button>
                        <a href="{{ route('client.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
