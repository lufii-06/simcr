@extends('dashboard')

@section('title', isset($status) ? 'Edit Developer Status' : 'Create Developer Status')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ isset($status) ? 'Edit Status' : 'Create Status' }}</div>
                </div>
                <form
                    action="{{ isset($status) ? route('developer-status.update', $status) : route('developer-status.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($status))
                        @method('PUT')
                    @endif
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('name') has-error @enderror">
                                    <label for="name" class="required">Status Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter status name (e.g. Lead Developer)" value="{{ old('name', $status->name ?? '') }}"
                                        required>
                                    @error('name')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('developer-status.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
