@extends('dashboard')

@section('title', $type == 'my' ? 'My Tasks' : 'All Project Tasks')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">{{ $type == 'my' ? 'My Tasks List' : 'All Project Tasks List' }}</h4>
                        @if ($type !== 'my')
                            <a href="{{ route('task.create') }}" class="btn btn-primary btn-round ms-auto">
                                <i class="fa fa-plus"></i>
                                Create Task
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="task-status-filter">Filter by Status:</label>
                            <select id="task-status-filter" class="form-control">
                                <option value="">All Status</option>
                                @foreach($tasks->pluck('status.name')->unique() as $statusName)
                                    @if($statusName)
                                        <option value="{{ $statusName }}">{{ $statusName }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="task-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Task Title</th>
                                    <th>Assignee</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tasks as $task)
                                    <tr>
                                        <td>{{ $task->project?->name ?? '-' }}</td>
                                        <td>{{ $task->title }}</td>
                                        <td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                                        <td>
                                            @php
                                                $badgeClass = 'badge-info';
                                                if (($task->status?->name ?? '') === 'To Do') {
                                                    $badgeClass = 'badge-primary';
                                                } elseif (($task->status?->name ?? '') === 'In Progress') {
                                                    $badgeClass = 'badge-warning';
                                                } elseif (($task->status?->name ?? '') === 'Done') {
                                                    $badgeClass = 'badge-success';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }} task-status-{{ $task->id }}">{{ $task->status?->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                        id="progress-bar-{{ $task->id }}"
                                                        style="width: {{ $task->progress }}%" 
                                                        aria-valuenow="{{ $task->progress }}" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small id="progress-text-{{ $task->id }}">{{ $task->progress_text }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-info btn-lg btn-detail"
                                                    data-id="{{ $task->getRouteKey() }}" data-bs-toggle="tooltip"
                                                    title="View Detail & Checklists">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No tasks found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Detail & Checklist Modal -->
    <div class="modal fade" id="taskDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <span class="fw-mediumbold">Task Detail</span>
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="task-info" class="mb-4"></div>
                    <hr>
                    <h6>Checklist Items</h6>
                    <div id="checklist-container">
                        <!-- Checklist items will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#task-datatables').DataTable({});

            $('#task-status-filter').on('change', function() {
                var val = $(this).val();
                table.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            // Toggle Checklist Item
            $(document).on('change', '.checklist-item-checkbox', function() {
                const checkbox = $(this);
                const id = checkbox.data('id');
                const taskId = checkbox.data('task-id');
                const isChecked = checkbox.is(':checked');
                
                $.post(`/task/checklist/${id}/toggle`, {
                    _token: '{{ csrf_token() }}'
                }, function(data) {
                    if (data.success) {
                        // Update progress bar on the list page
                        $(`#progress-bar-${taskId}`).css('width', data.progress + '%');
                        $(`#progress-bar-${taskId}`).attr('aria-valuenow', data.progress);
                        $(`#progress-text-${taskId}`).text(data.progress_text);
                        
                        // Update status on table and modal
                        let badgeClass = 'badge-info';
                        if (data.status_name === 'To Do') {
                             badgeClass = 'badge-primary';
                        } else if (data.status_name === 'In Progress') {
                             badgeClass = 'badge-warning';
                        } else if (data.status_name === 'Done') {
                             badgeClass = 'badge-success';
                        }

                        $(`.task-status-${taskId}`)
                            .removeClass('badge-primary badge-warning badge-success badge-info')
                            .addClass(badgeClass)
                            .text(data.status_name);
                        $(`.modal-task-status-${taskId}`)
                            .removeClass('badge-primary badge-warning badge-success badge-info')
                            .addClass(badgeClass)
                            .text(data.status_name);
                        
                        $.notify({
                            icon: 'fa fa-check',
                            title: 'Updated',
                            message: 'Checklist item updated successfully',
                        }, {
                            type: 'success',
                            time: 1000,
                        });
                    }
                }).fail(function(xhr) {
                    $.notify({
                        icon: 'fa fa-times',
                        title: 'Error',
                        message: xhr.responseJSON?.error || 'Error toggling checklist item',
                    }, {
                        type: 'danger',
                        time: 1000,
                    });
                    checkbox.prop('checked', !isChecked); // revert
                });
            });

            // Show Task Detail
            $('.btn-detail').on('click', function() {
                const id = $(this).data('id');
                const modal = $('#taskDetailModal');
                
                $('#task-info').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
                $('#checklist-container').html('');
                modal.modal('show');

                $.get(`/task/${id}`, function(task) {
                    let badgeClass = 'badge-info';
                    if (task.status.name === 'To Do') {
                        badgeClass = 'badge-primary';
                    } else if (task.status.name === 'In Progress') {
                        badgeClass = 'badge-warning';
                    } else if (task.status.name === 'Done') {
                        badgeClass = 'badge-success';
                    }

                    let infoHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Project:</strong> ${task.project.name}</p>
                                <p><strong>Title:</strong> ${task.title}</p>
                                <p><strong>Status:</strong> <span class="badge ${badgeClass} modal-task-status-${task.id}">${task.status.name}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Created By:</strong> ${task.creator.name}</p>
                                <p><strong>Assignee:</strong> ${task.assignee.name}</p>
                            </div>
                            <div class="col-md-12">
                                <p><strong>Description:</strong><br>${task.description || 'No description'}</p>
                            </div>
                        </div>
                    `;
                    $('#task-info').html(infoHtml);

                    let checklistHtml = '<ul class="list-group list-group-flush">';
                    if (task.checklists.length > 0) {
                        task.checklists.forEach(item => {
                            const isChecked = item.is_completed ? 'checked' : '';
                            // Only assignee can edit
                            const disabled = {{ auth()->id() }} == task.assigned_to ? '' : 'disabled';
                            
                            checklistHtml += `
                                <li class="list-group-item d-flex align-items-center">
                                    <div class="form-check me-2">
                                        <input class="form-check-input checklist-item-checkbox" type="checkbox" 
                                            data-id="${item.id}" data-task-id="${task.id}"
                                            ${isChecked} ${disabled}>
                                    </div>
                                    <span class="${item.is_completed ? 'text-muted text-decoration-line-through' : ''}">
                                        <strong class="text-primary me-1">[${item.code}]</strong> ${item.item_text}
                                    </span>
                                </li>
                            `;
                        });
                    } else {
                        checklistHtml += '<li class="list-group-item text-muted">No checklist items.</li>';
                    }
                    checklistHtml += '</ul>';
                    $('#checklist-container').html(checklistHtml);
                });
            });
        });
    </script>
@endpush
