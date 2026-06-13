@extends('dashboard')

@section('title', 'Open a Merge Request')

@section('content')
<div class="page-inner">
    <div class="row">
        <div class="col-md-8 mx-auto">
            @if (session('error'))
                <div class="alert alert-danger mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="card-title fw-bold">
                        <i class="fas fa-code-branch me-2 text-primary"></i> Create Merge Request
                    </div>
                    <p class="text-muted mb-0 small">Propose changes from a source branch to be merged into a target branch.</p>
                </div>
                <form action="{{ route('repository.merge-requests.store', $repository->name) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Branch Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="target_branch" class="form-label fw-bold text-muted small">Base Branch (Target)</label>
                                <select name="target_branch" id="target_branch" class="form-select @error('target_branch') is-invalid @enderror" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch }}" {{ old('target_branch', $repository->default_branch) === $branch ? 'selected' : '' }}>
                                            {{ $branch }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('target_branch')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="source_branch" class="form-label fw-bold text-muted small">Compare Branch (Source)</label>
                                <select name="source_branch" id="source_branch" class="form-select @error('source_branch') is-invalid @enderror" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch }}" {{ old('source_branch') === $branch ? 'selected' : '' }}>
                                            {{ $branch }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('source_branch')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- PR Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold text-muted small">Title</label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Implement user profile dashboard view" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- PR Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold text-muted small">Description</label>
                            <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Describe the changes made and link any relevant tasks..."></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-action d-flex justify-content-end bg-light">
                        <a href="{{ route('repository.show', ['repository' => $repository->name, 'tab' => 'pulls']) }}" class="btn btn-secondary me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Create Merge Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
