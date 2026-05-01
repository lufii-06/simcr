@extends('dashboard')

@section('title', isset($specialization) ? 'Edit Specialization' : 'Create Specialization')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ isset($specialization) ? 'Edit Specialization' : 'Create New Specialization' }}</div>
                </div>
                <form action="{{ isset($specialization) ? route('specialization.update', $specialization) : route('specialization.store') }}" method="POST">
                    @csrf
                    @if (isset($specialization))
                        @method('PUT')
                    @endif
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('name') has-error @enderror">
                                    <label for="name" class="required">Specialization Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="e.g. Backend Developer" value="{{ old('name', $specialization->name ?? '') }}" required>
                                    @error('name')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button type="submit" class="btn btn-success">Submit</button>
                        <a href="{{ route('specialization.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
