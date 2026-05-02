@extends('dashboard')

@section('title', isset($status) ? 'Edit Task Status' : 'Create Task Status')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ isset($status) ? 'Edit Status' : 'Create Status' }}</div>
                </div>
                <form
                    action="{{ isset($status) ? route('task-status.update', $status) : route('task-status.store') }}"
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
                                        placeholder="Enter status name (e.g. To Do)" value="{{ old('name', $status->name ?? '') }}"
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
                        <a href="{{ route('task-status.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
