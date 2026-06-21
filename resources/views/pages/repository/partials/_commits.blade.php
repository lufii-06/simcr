<div class="tab-pane fade" id="pills-commits" role="tabpanel" aria-labelledby="pills-commits-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <h6 class="fw-bold mb-0">History for branch: <span class="text-info">{{ $selectedBranch }}</span></h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Hash</th>
                    <th>Message</th>
                    <th>Author</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentCommits as $commit)
                    <tr>
                        <td>
                            <a href="#" class="commit-hash-link" data-hash="{{ $commit['hash'] }}"
                                title="View Commit Details">
                                <code class="text-primary fw-bold">{{ substr($commit['hash'], 0, 7) }}</code>
                            </a>
                        </td>
                        <td>
                            {{ $commit['message'] }}
                        </td>
                        <td>{{ $commit['author'] }}</td>
                        <td class="text-nowrap">{{ $commit['date'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">No commits found for
                            this branch.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>