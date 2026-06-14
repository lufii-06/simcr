@extends('dashboard')

@section('title', isset($task) ? 'Edit Task' : 'Create Task')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ isset($task) ? 'Edit Task' : 'Create New Task' }}</div>
                </div>
                <form action="{{ isset($task) ? route('task.update', $task) : route('task.store') }}" method="POST">
                    @csrf
                    @if (isset($task))
                        @method('PUT')
                    @endif
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('project_id') has-error @enderror">
                                    <label for="project_id" class="required">Project</label>
                                    @if(isset($selectedProjectId))
                                        <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
                                    @endif
                                    <select class="form-control" id="project_id" name="{{ isset($selectedProjectId) ? '' : 'project_id' }}" required {{ isset($selectedProjectId) ? 'disabled' : '' }}>
                                        <option value="">Select Project</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}" data-route-key="{{ $project->getRouteKey() }}"
                                                {{ old('project_id', $selectedProjectId ?? $task->project_id ?? '') == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group @error('assigned_to') has-error @enderror">
                                    <label for="assigned_to" class="required">Assign To</label>
                                    <select class="form-control" id="assigned_to" name="assigned_to" required>
                                        <option value="">Select Assignee (Select Project first)</option>
                                        @if(isset($task))
                                            <!-- Pre-fill for edit if needed -->
                                        @endif
                                    </select>
                                    @error('assigned_to')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group @error('task_status_id') has-error @enderror">
                                    <label for="task_status_id" class="required">Initial Status</label>
                                    <select class="form-control" id="task_status_id" name="task_status_id" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}"
                                                {{ old('task_status_id', $task->task_status_id ?? '') == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('task_status_id')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group @error('title') has-error @enderror">
                                    <label for="title" class="required">Task Title</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        placeholder="Enter task title" value="{{ old('title', $task->title ?? '') }}" required>
                                    @error('title')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"
                                        placeholder="Enter task description">{{ old('description', $task->description ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="fw-bold mb-2">Checklist Items</label>
                                <div id="checklist-wrapper">
                                    <div class="input-group mb-2 checklist-item">
                                        <input type="text" name="checklists[]" class="form-control" placeholder="Checklist item description...">
                                        <button class="btn btn-danger remove-checklist" type="button"><i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-info mt-2" id="add-checklist">
                                    <i class="fa fa-plus"></i> Add Checklist Item
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button type="submit" class="btn btn-success">Save Task</button>
                        @php
                            $cancelUrl = route('task.index');
                            if (isset($selectedProjectId)) {
                                $selectedProjectModel = $projects->firstWhere('id', $selectedProjectId);
                                if ($selectedProjectModel) {
                                    $cancelUrl = route('task.index', ['project_id' => $selectedProjectModel->getRouteKey()]);
                                }
                            }
                        @endphp
                        <a href="{{ $cancelUrl }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Dynamic Checklist
            $('#add-checklist').on('click', function() {
                const html = `
                    <div class="input-group mb-2 checklist-item">
                        <input type="text" name="checklists[]" class="form-control" placeholder="Checklist item description...">
                        <button class="btn btn-danger remove-checklist" type="button"><i class="fa fa-minus"></i></button>
                    </div>
                `;
                $('#checklist-wrapper').append(html);
            });

            $(document).on('click', '.remove-checklist', function() {
                if ($('.checklist-item').length > 1) {
                    $(this).closest('.checklist-item').remove();
                } else {
                    $(this).closest('.checklist-item').find('input').val('');
                }
            });

            // Dependent Dropdown for Assignee
            $('#project_id').on('change', function() {
                const selectedOption = $(this).find(':selected');
                const projectRouteKey = selectedOption.data('route-key');
                if (projectRouteKey) {
                    $('#assigned_to').html('<option value="">Loading users...</option>');
                    $.get(`/project/${projectRouteKey}/users`, function(users) {
                        let options = '<option value="">Select Assignee</option>';
                        const oldAssignedTo = '{{ old('assigned_to', $task->assigned_to ?? '') }}';
                        const userList = Array.isArray(users) ? users : Object.values(users);
                        userList.forEach(user => {
                            const selected = oldAssignedTo == user.id ? 'selected' : '';
                            options += `<option value="${user.id}" ${selected}>${user.name} (${user.role})</option>`;
                        });
                        $('#assigned_to').html(options);
                    });
                } else {
                    $('#assigned_to').html('<option value="">Select Project first</option>');
                }
            });

            // Trigger change if editing or old input exists
            if ($('#project_id').val()) {
                $('#project_id').trigger('change');
            }
        });
    </script>
@endpush
