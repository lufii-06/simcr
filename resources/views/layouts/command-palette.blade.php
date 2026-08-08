@auth
@php
    $role = auth()->user()->role;
    $navItems = [];

    // General / Main Navigation
    $navItems[] = ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'fas fa-home', 'category' => 'Main Navigation'];
    $navItems[] = ['title' => 'My Profile', 'url' => route('profile.index'), 'icon' => 'fas fa-user-circle', 'category' => 'Account'];

    // Master Data (PM only)
    if ($role === 'pm') {
        $navItems[] = ['title' => 'User Management', 'url' => route('user.index'), 'icon' => 'fas fa-user', 'category' => 'Master Data'];
        $navItems[] = ['title' => 'Client Management', 'url' => route('client.index'), 'icon' => 'fas fa-users', 'category' => 'Master Data'];
        $navItems[] = ['title' => 'Developer Management', 'url' => route('developer.index'), 'icon' => 'fas fa-user-tag', 'category' => 'Master Data'];
    }

    // Projects, Tasks & Repositories (PM, Developer, Client)
    if ($role !== 'owner') {
        $navItems[] = ['title' => 'List Projects', 'url' => route('project.index'), 'icon' => 'fas fa-project-diagram', 'category' => 'Projects & Tasks'];
        $navItems[] = ['title' => 'Project Analytics', 'url' => route('project.index') . '?view=analytics', 'icon' => 'fas fa-chart-bar', 'category' => 'Projects & Tasks'];
        
        if ($role !== 'client') {
            $navItems[] = ['title' => 'Repositories', 'url' => route('repository.index'), 'icon' => 'fas fa-code-branch', 'category' => 'Projects & Tasks'];
        }

        $navItems[] = ['title' => 'All Tasks', 'url' => route('task.index'), 'icon' => 'fas fa-tasks', 'category' => 'Projects & Tasks'];
        
        if ($role !== 'client') {
            $navItems[] = ['title' => 'My Tasks', 'url' => route('task.index', ['type' => 'my']), 'icon' => 'fas fa-user-check', 'category' => 'Projects & Tasks'];
            $navItems[] = ['title' => 'Task Log', 'url' => route('task.log'), 'icon' => 'fas fa-history', 'category' => 'Projects & Tasks'];
        }

        // Quick Actions
        if ($role === 'pm') {
            $navItems[] = ['title' => 'Create New Project', 'url' => route('project.create'), 'icon' => 'fas fa-plus-circle', 'category' => 'Quick Actions'];
            $navItems[] = ['title' => 'Add New User', 'url' => route('user.create'), 'icon' => 'fas fa-user-plus', 'category' => 'Quick Actions'];
            $navItems[] = ['title' => 'Add New Client', 'url' => route('client.create'), 'icon' => 'fas fa-user-tie', 'category' => 'Quick Actions'];
            $navItems[] = ['title' => 'Add New Developer', 'url' => route('developer.create'), 'icon' => 'fas fa-user-shield', 'category' => 'Quick Actions'];
        }
        if ($role === 'pm' || $role === 'developer') {
            $navItems[] = ['title' => 'Create New Task', 'url' => route('task.create'), 'icon' => 'fas fa-tasks', 'category' => 'Quick Actions'];
            $navItems[] = ['title' => 'Export My Tasks (PDF)', 'url' => route('task.export.my-tasks-pdf'), 'icon' => 'fas fa-file-pdf', 'category' => 'Quick Actions'];
        }
    }

    // Reports (PM & Owner)
    if ($role === 'pm' || $role === 'owner') {
        $navItems[] = ['title' => 'User Reports', 'url' => route('report.master'), 'icon' => 'fas fa-file-contract', 'category' => 'Reports'];
        $navItems[] = ['title' => 'Project Reports', 'url' => route('report.project'), 'icon' => 'fas fa-file-invoice', 'category' => 'Reports'];
        $navItems[] = ['title' => 'Task Reports', 'url' => route('report.task'), 'icon' => 'fas fa-file-alt', 'category' => 'Reports'];
        $navItems[] = ['title' => 'Repository Reports', 'url' => route('report.repository'), 'icon' => 'fas fa-file-code', 'category' => 'Reports'];
    }

    // Settings (PM only)
    if ($role === 'pm') {
        $navItems[] = ['title' => 'Developer Status Setting', 'url' => route('developer-status.index'), 'icon' => 'fas fa-cog', 'category' => 'Settings'];
        $navItems[] = ['title' => 'Project Status Setting', 'url' => route('project-status.index'), 'icon' => 'fas fa-cog', 'category' => 'Settings'];
        $navItems[] = ['title' => 'Task Status Setting', 'url' => route('task-status.index'), 'icon' => 'fas fa-cog', 'category' => 'Settings'];
        $navItems[] = ['title' => 'Specialization Setting', 'url' => route('specialization.index'), 'icon' => 'fas fa-cog', 'category' => 'Settings'];
    }
@endphp

<!-- Command Palette Modal -->
<div class="modal fade" id="commandPaletteModal" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(8px); background-color: rgba(15, 23, 42, 0.65);">
    <div class="modal-dialog modal-dialog-top" style="max-width: 640px; margin-top: 5rem;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: #1e1e2e; color: #cdd6f4;">
            <div class="modal-body p-0">
                <!-- Search Input Header -->
                <div class="d-flex align-items-center px-3 py-3 border-bottom border-secondary border-opacity-25">
                    <i class="fas fa-search me-3 text-primary" style="font-size: 1.2rem;"></i>
                    <input type="text" id="cmdPaletteSearch" class="form-control bg-transparent border-0 text-light shadow-none ps-0" placeholder="Type a navigation menu or action name..." autocomplete="off" style="font-size: 1.05rem;">
                    <kbd class="badge bg-secondary bg-opacity-25 text-light px-2 py-1 border border-secondary border-opacity-50 me-1" style="font-size: 0.75rem;">ESC</kbd>
                </div>

                <!-- Navigation Menu Items List -->
                <div id="cmdPaletteList" class="p-2" style="max-height: 380px; overflow-y: auto;">
                    @php
                        $grouped = collect($navItems)->groupBy('category');
                    @endphp

                    @foreach ($grouped as $category => $items)
                        <div class="cmd-category-group mb-2">
                            <div class="px-3 py-1 text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 1px;">
                                {{ $category }}
                            </div>
                            @foreach ($items as $item)
                                <a href="{{ $item['url'] }}" class="cmd-item d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 text-decoration-none text-light mb-1" data-title="{{ strtolower($item['title']) }}" data-category="{{ strtolower($category) }}">
                                    <div class="d-flex align-items-center">
                                        <div class="cmd-icon-box me-3 d-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; background: rgba(255, 255, 255, 0.06);">
                                            <i class="{{ $item['icon'] }} text-primary"></i>
                                        </div>
                                        <span class="cmd-title fw-medium" data-original-title="{{ $item['title'] }}">{{ $item['title'] }}</span>
                                    </div>
                                    <i class="fas fa-arrow-right opacity-0 cmd-arrow me-1" style="font-size: 0.85rem; transition: opacity 0.15s ease;"></i>
                                </a>
                            @endforeach
                        </div>
                    @endforeach

                    <!-- Empty Search State -->
                    <div id="cmdPaletteEmpty" class="text-center py-4 d-none">
                        <i class="fas fa-search-minus fa-2x text-muted mb-2 opacity-50"></i>
                        <p class="text-muted mb-0">No matching navigation menu found.</p>
                    </div>
                </div>

                <!-- Footer Help -->
                <div class="d-flex align-items-center justify-content-between px-3 py-2.5 bg-dark bg-opacity-50 border-top border-secondary border-opacity-25 text-muted" style="font-size: 0.8rem;">
                    <div class="d-flex gap-3">
                        <span><kbd class="kbd-sm">↑</kbd> <kbd class="kbd-sm">↓</kbd> to navigate</span>
                        <span><kbd class="kbd-sm">↵</kbd> to select</span>
                    </div>
                    <span><kbd class="kbd-sm">ESC</kbd> to close</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .cmd-item {
        transition: background-color 0.15s ease, transform 0.1s ease;
    }
    .cmd-item:hover, .cmd-item.active {
        background-color: rgba(99, 102, 241, 0.22) !important;
        color: #ffffff !important;
    }
    .cmd-item:hover .cmd-arrow, .cmd-item.active .cmd-arrow {
        opacity: 1 !important;
    }
    .cmd-item:hover .cmd-icon-box, .cmd-item.active .cmd-icon-box {
        background: #6366f1 !important;
    }
    .cmd-item:hover .cmd-icon-box i, .cmd-item.active .cmd-icon-box i {
        color: #ffffff !important;
    }
    .cmd-highlight {
        color: #3b82f6 !important;
        font-weight: 700 !important;
        background: rgba(59, 130, 246, 0.18);
        padding: 1px 4px;
        border-radius: 4px;
    }
    .kbd-sm {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        padding: 1px 5px;
        font-size: 0.75rem;
        color: #e2e8f0;
    }
    .btn-cmd-trigger {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #94a3b8;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-cmd-trigger:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.25);
    }
    .cmd-kbd-badge {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #cbd5e1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('commandPaletteModal');
        if (!modalEl) return;

        let modalInstance = null;
        function getModal() {
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(modalEl);
            }
            return modalInstance;
        }

        const searchInput = document.getElementById('cmdPaletteSearch');
        const items = Array.from(document.querySelectorAll('.cmd-item'));
        const emptyState = document.getElementById('cmdPaletteEmpty');
        let selectedIndex = -1;

        window.openCommandPalette = function () {
            getModal().show();
        };

        // Global hotkey Ctrl + K or Cmd + K
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                openCommandPalette();
            }
        });

        // Auto focus search input on show
        modalEl.addEventListener('shown.bs.modal', function () {
            if (searchInput) {
                searchInput.value = '';
                filterItems('');
                searchInput.focus();
            }
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                filterItems(this.value);
            });

            // Keyboard navigation (Up / Down / Enter)
            searchInput.addEventListener('keydown', function (e) {
                const visibleItems = items.filter(item => !item.classList.contains('d-none'));
                if (visibleItems.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex + 1) % visibleItems.length;
                    updateSelection(visibleItems);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex - 1 + visibleItems.length) % visibleItems.length;
                    updateSelection(visibleItems);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (selectedIndex >= 0 && visibleItems[selectedIndex]) {
                        visibleItems[selectedIndex].click();
                    } else if (visibleItems.length > 0) {
                        visibleItems[0].click();
                    }
                }
            });
        }

        function filterItems(rawQuery) {
            const query = rawQuery.trim().toLowerCase();
            const queryWords = query.split(/\s+/).filter(w => w.length > 0);
            let visibleCount = 0;
            const groups = document.querySelectorAll('.cmd-category-group');

            groups.forEach(group => {
                let groupVisibleCount = 0;
                const groupItems = group.querySelectorAll('.cmd-item');

                groupItems.forEach(item => {
                    const title = item.getAttribute('data-title') || '';
                    const category = item.getAttribute('data-category') || '';
                    const titleEl = item.querySelector('.cmd-title');
                    const originalTitle = titleEl ? (titleEl.getAttribute('data-original-title') || titleEl.textContent) : '';

                    // LIKE matching: every word in query must exist in title or category
                    const isMatch = queryWords.length === 0 || queryWords.every(word => title.includes(word) || category.includes(word));

                    if (isMatch) {
                        item.classList.remove('d-none');
                        groupVisibleCount++;
                        visibleCount++;

                        // Highlight matching words/letters in blue
                        if (titleEl && queryWords.length > 0) {
                            const escapedWords = queryWords.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
                            const pattern = new RegExp(`(${escapedWords.join('|')})`, 'gi');
                            titleEl.innerHTML = originalTitle.replace(pattern, '<span class="cmd-highlight">$1</span>');
                        } else if (titleEl) {
                            titleEl.textContent = originalTitle;
                        }
                    } else {
                        item.classList.add('d-none');
                        if (titleEl) {
                            titleEl.textContent = originalTitle;
                        }
                    }
                });

                if (groupVisibleCount === 0) {
                    group.classList.add('d-none');
                } else {
                    group.classList.remove('d-none');
                }
            });

            if (visibleCount === 0 && emptyState) {
                emptyState.classList.remove('d-none');
            } else if (emptyState) {
                emptyState.classList.add('d-none');
            }

            resetSelection();
        }

        function resetSelection() {
            selectedIndex = -1;
            items.forEach(item => item.classList.remove('active'));
        }

        function updateSelection(visibleItems) {
            items.forEach(item => item.classList.remove('active'));
            if (selectedIndex >= 0 && visibleItems[selectedIndex]) {
                const el = visibleItems[selectedIndex];
                el.classList.add('active');
                el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        }
    });
</script>
@endauth
