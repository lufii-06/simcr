@extends('dashboard')

@section('title', isset($project) ? 'Edit Project' : 'Create Project')

@section('content')
    <div class="page-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">{{ isset($project) ? 'Modify Project Data' : 'Add New Project' }}</div>
                    </div>
                    <form action="{{ isset($project) ? route('project.update', $project) : route('project.store') }}"
                        method="POST">
                        @csrf
                        @if (isset($project))
                            @method('PUT')
                        @endif
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 border-end">
                                    <h5 class="fw-bold mb-3">Basic Information</h5>

                                    <div class="form-group @error('name') has-error @enderror">
                                        <label for="name" class="required">Project Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Enter project name"
                                            value="{{ old('name', $project->name ?? '') }}" required>
                                        @error('name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group @error('client_id') has-error @enderror">
                                        <label for="client_id" class="required">Client</label>
                                        <select class="form-select w-100" data-bs-toggle="select" id="client_id"
                                            name="client_id" required>

                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}"
                                                    {{ old('client_id', $project->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                                    {{ $client->user->name ?? 'Unknown' }} - {{ $client->company_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('client_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>


                                    <div class="form-group @error('project_status_id') has-error @enderror">
                                        <label for="project_status_id" class="required">Project Status</label>
                                        <select class="form-select w-100" data-bs-toggle="select" id="project_status_id"
                                            name="project_status_id" required>

                                            @foreach ($projectStatuses as $status)
                                                <option value="{{ $status->id }}"
                                                    {{ old('project_status_id', $project->project_status_id ?? '') == $status->id ? 'selected' : '' }}>
                                                    {{ $status->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('project_status_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group @error('start_date') has-error @enderror">
                                                <label for="start_date">Start Date</label>
                                                <input type="date" class="form-control" id="start_date" name="start_date"
                                                    value="{{ old('start_date', $project->start_date ?? '') }}">
                                                @error('start_date')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group @error('end_date') has-error @enderror">
                                                <label for="end_date">End Date</label>
                                                <input type="date" class="form-control" id="end_date" name="end_date"
                                                    value="{{ old('end_date', $project->end_date ?? '') }}">
                                                @error('end_date')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group @error('description') has-error @enderror">
                                        <label for="description">Detailed Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="5"
                                            placeholder="Enter project details and scope...">{{ old('description', $project->description ?? '') }}</textarea>
                                        @error('description')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                                        Assigned Developers
                                        <button type="button" class="btn btn-sm btn-success btn-round"
                                            id="addDeveloperBtn">
                                            <i class="fa fa-plus"></i> Add Developer
                                        </button>
                                    </h5>

                                    <div id="developerContainer">
                                        <!-- Dynamic Rows injected here via JS -->
                                    </div>

                                    <!-- Hidden template for cloning -->
                                    <div id="developerTemplate" class="d-none">
                                        <div class="row mb-3 developer-row align-items-center p-2 border rounded mx-1">
                                            <div class="col-md-5">
                                                <label class="form-label mb-1 required">Select User</label>
                                                <select class="form-select user-select selectpicker" data-live-search="true"
                                                    name="developer_ids[]" required disabled>
                                                    @foreach ($users as $user)
                                                        @if ($user->role !== 'client' && $user->role !== 'owner')
                                                            <option value="{{ $user->id }}">{{ $user->name }}
                                                                ({{ $user->role }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label mb-1 required">Project Role</label>
                                                <select class="form-select status-select selectpicker"
                                                    data-live-search="true" name="developer_statuses[]" required disabled>
                                                    @foreach ($developerStatuses as $status)
                                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 mt-4 text-end">
                                                <button type="button" class="btn btn-danger btn-sm remove-developer"><i
                                                        class="fa fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ route('project.index') }}" class="btn btn-danger">Cancel</a>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                let devIndex = 0;

                // Function to add a new developer row
                function addDeveloperRow(userId = '', roleId = '') {
                    let $template = $('#developerTemplate').children().clone();

                    // Update names to include index for array submission and enable the element
                    $template.find('.user-select').attr('name', `developers[${devIndex}][user_id]`).prop('disabled',
                        false).val(userId);
                    $template.find('.status-select').attr('name', `developers[${devIndex}][developer_status_id]`).prop(
                        'disabled', false).val(roleId);

                    $('#developerContainer').append($template);

                    // Initialize bsSelectDrop for the newly cloned row
                    $template.find('.user-select').bsSelectDrop();
                    $template.find('.status-select').bsSelectDrop();

                    devIndex++;
                }

                // Add button click event
                $('#addDeveloperBtn').on('click', function() {
                    addDeveloperRow();
                });

                // Remove button click event
                $(document).on('click', '.remove-developer', function() {
                    $(this).closest('.developer-row').remove();
                });

                // Load existing developers if in Edit mode
                @if (isset($project) && $project->developers)
                    let existingDevs = @json($project->developers);
                    existingDevs.forEach(function(dev) {
                        addDeveloperRow(dev.user_id, dev.developer_status_id);
                    });
                @elseif (old('developers'))
                    // Load old input if validation failed
                    let oldDevs = @json(old('developers'));
                    if (oldDevs) {
                        Object.values(oldDevs).forEach(function(dev) {
                            addDeveloperRow(dev.user_id, dev.developer_status_id);
                        });
                    }
                @endif
            });
        </script>
    @endpush
@endsection
