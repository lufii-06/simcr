<div class="tab-pane fade" id="pills-guide" role="tabpanel" aria-labelledby="pills-guide-tab">
    <div class="p-3">
        <h5 class="fw-bold mb-3">How to Clone this Repository</h5>

        <div class="accordion accordion-secondary">
            <div class="card">
                <div class="card-header" id="headingOne" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                    aria-expanded="true" aria-controls="collapseOne">
                    <div class="span-icon">
                        <div class="fas fa-key"></div>
                    </div>
                    <div class="span-title">
                        Option 1: Using SSH (Recommended)
                    </div>
                    <div class="span-mode"></div>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne">
                    <div class="card-body">
                        <p class="small text-muted">SSH is the most secure way to interact with Git without entering
                            credentials every time.</p>

                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded"
                                id="guide-ssh"><code>git clone {{ env('REPO_ROOT_URL', 'git@localhost') }}:repositories/{{ $repository->name }}.git</code></pre>
                            <button class="btn btn-xs btn-primary position-absolute" style="top: 10px; right: 10px;"
                                onclick="copyToClipboard('guide-ssh')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>

                        <h6 class="fw-bold small mt-3">Step-by-step:</h6>
                        <ol class="small ps-3">
                            <li>Ensure you have an SSH Key on your computer. Check with <code>ls -al ~/.ssh</code>.</li>
                            <li>If you don't have one, generate it using:
                                <code>ssh-keygen -t ed25519 -C "your_email@example.com"</code>.</li>
                            <li>Add your **Public Key** (<code>id_ed25519.pub</code>) to your SIMCR User Profile.</li>
                            <li>Copy the clone command above and run it in your terminal.</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header collapsed" id="headingTwo" data-bs-toggle="collapse"
                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <div class="span-icon">
                        <div class="fas fa-link"></div>
                    </div>
                    <div class="span-title">
                        Option 2: Using HTTP
                    </div>
                    <div class="span-mode"></div>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo">
                    <div class="card-body">
                        @if ($repository->is_public)
                            <p class="text-success small"><i class="fas fa-check-circle"></i> This
                                repository is <b>Public</b>. Anyone with the URL can clone it.</p>
                        @else
                            <p class="text-warning small"><i class="fas fa-exclamation-circle"></i> This repository is
                                <b>Private</b>.</p>

                            @if ($repository->access_token)
                                <div class="alert alert-info py-2 small">
                                    <i class="fas fa-info-circle"></i> <b>Access Token:</b> We have embedded your token
                                    into the URL. You won't be asked for a password.
                                </div>
                            @else
                                <div class="alert alert-danger py-2 small">
                                    <i class="fas fa-exclamation-triangle"></i> <b>Action Required!</b> This is a
                                    private repository. Please generate an Access Token in <b>Settings</b> first.
                                    <div class="mt-2 text-end">
                                        <button class="btn btn-xs btn-danger" onclick="showTab('pills-settings')">Generate
                                            Token Now</button>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded" id="guide-http"><code>git clone {{ $httpUrl }}</code></pre>
                            <button class="btn btn-xs btn-secondary position-absolute" style="top: 10px; right: 10px;"
                                onclick="copyToClipboard('guide-http')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>

                        <h6 class="fw-bold small mt-3">Step-by-step:</h6>
                        <ol class="small ps-3">
                            <li>Copy the HTTP URL provided above.</li>
                            @if (!$repository->is_public)
                                <li>Ensure your Access Token is active (Check the Settings tab).</li>
                            @endif
                            <li>Paste the command in your terminal and press Enter.</li>
                            <li>If prompted for a password, use your **Access Token** (or leave blank if it's already in
                                the URL).</li>
                        </ol>

                        <div class="bg-light p-2 mt-3 rounded border">
                            <p class="small mb-0 text-muted"><b>Note:</b> Access tokens are a more secure alternative to
                                using your account password for Git operations.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========= COMMIT MESSAGE STANDARD ========= --}}
        <h5 class="fw-bold mb-3 mt-4">Commit Message Standard</h5>
        <div class="alert alert-warning py-2 small mb-3">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <b>Wajib!</b> Semua commit yang di-push ke repository ini harus mengikuti format berikut. Push akan <b>ditolak</b> jika format tidak sesuai.
        </div>

        <div class="accordion accordion-secondary">
            <div class="card">
                <div class="card-header" id="headingCommit1" data-bs-toggle="collapse" data-bs-target="#collapseCommit1"
                    aria-expanded="true" aria-controls="collapseCommit1">
                    <div class="span-icon">
                        <div class="fas fa-tag"></div>
                    </div>
                    <div class="span-title">
                        Format Dasar (tanpa checklist)
                    </div>
                    <div class="span-mode"></div>
                </div>
                <div id="collapseCommit1" class="collapse show" aria-labelledby="headingCommit1">
                    <div class="card-body">
                        <p class="small text-muted">Gunakan format ini untuk commit umum yang hanya berhubungan dengan sebuah task, tanpa menyelesaikan checklist item tertentu.</p>
                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded" id="guide-commit-short"><code>[feat|fix] : pesan commit Anda [TASK-XXX]</code></pre>
                            <button class="btn btn-xs btn-secondary position-absolute" style="top: 10px; right: 10px;"
                                onclick="copyToClipboard('guide-commit-short')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <h6 class="fw-bold small mt-3">Contoh:</h6>
                        <pre class="bg-dark text-light p-2 rounded small"><code>git commit -m "[feat] : setup struktur folder project [TASK-001]"
git commit -m "[fix] : perbaiki bug pada halaman login [TASK-003]"</code></pre>
                        <h6 class="fw-bold small mt-3">Aturan:</h6>
                        <ul class="small ps-3">
                            <li>Type hanya boleh: <code>feat</code> atau <code>fix</code></li>
                            <li><code>[TASK-XXX]</code> harus berupa kode task yang ada dan ter-assign ke Anda</li>
                            <li>Pesan commit tidak boleh kosong</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header collapsed" id="headingCommit2" data-bs-toggle="collapse"
                    data-bs-target="#collapseCommit2" aria-expanded="false" aria-controls="collapseCommit2">
                    <div class="span-icon">
                        <div class="fas fa-check-square"></div>
                    </div>
                    <div class="span-title">
                        Format dengan Checklist Item
                    </div>
                    <div class="span-mode"></div>
                </div>
                <div id="collapseCommit2" class="collapse" aria-labelledby="headingCommit2">
                    <div class="card-body">
                        <p class="small text-muted">Gunakan format ini jika commit Anda menyelesaikan atau mengerjakan sebuah checklist item. Status checklist akan otomatis diperbarui di sistem.</p>
                        <div class="position-relative">
                            <pre class="bg-dark text-light p-3 rounded" id="guide-commit-full"><code>[feat|fix] : pesan commit Anda [TASK-XXX] [CK-XXX] [FINISH|UNFINISH]</code></pre>
                            <button class="btn btn-xs btn-secondary position-absolute" style="top: 10px; right: 10px;"
                                onclick="copyToClipboard('guide-commit-full')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <h6 class="fw-bold small mt-3">Contoh:</h6>
                        <pre class="bg-dark text-light p-2 rounded small"><code>git commit -m "[feat] : implementasi form login [TASK-001] [CK-003] [FINISH]"
git commit -m "[fix] : perbaiki validasi email (WIP) [TASK-001] [CK-003] [UNFINISH]"</code></pre>
                        <h6 class="fw-bold small mt-3">Aturan:</h6>
                        <ul class="small ps-3">
                            <li><code>[CK-XXX]</code> harus berupa kode checklist item yang benar-benar ada di task tersebut</li>
                            <li><code>[FINISH]</code> → checklist item akan ditandai <b>selesai</b> secara otomatis</li>
                            <li><code>[UNFINISH]</code> → checklist item akan ditandai <b>belum selesai</b></li>
                            <li>Jika <code>[CK-XXX]</code> disertakan, maka <code>[FINISH|UNFINISH]</code> <b>wajib ada</b></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header collapsed" id="headingCommit3" data-bs-toggle="collapse"
                    data-bs-target="#collapseCommit3" aria-expanded="false" aria-controls="collapseCommit3">
                    <div class="span-icon">
                        <div class="fas fa-times-circle"></div>
                    </div>
                    <div class="span-title">
                        Alasan Push Bisa Ditolak
                    </div>
                    <div class="span-mode"></div>
                </div>
                <div id="collapseCommit3" class="collapse" aria-labelledby="headingCommit3">
                    <div class="card-body">
                        <p class="small text-muted">Push Anda akan ditolak jika salah satu commit memiliki kondisi berikut:</p>
                        <ul class="small ps-3">
                            <li>Format commit tidak sesuai standar (tidak ada type, tidak ada TASK-ID, dll)</li>
                            <li>Kode <code>[TASK-XXX]</code> tidak ditemukan di sistem</li>
                            <li>Task tidak termasuk dalam project repository ini</li>
                            <li>Task tidak ter-assign ke Anda (khusus role Developer)</li>
                            <li>Kode <code>[CK-XXX]</code> tidak ditemukan atau tidak termasuk dalam task yang disebutkan</li>
                            <li><code>[CK-XXX]</code> disertakan tapi <code>[FINISH|UNFINISH]</code> tidak ada</li>
                        </ul>
                        <div class="bg-light p-2 mt-2 rounded border">
                            <p class="small mb-0 text-muted"><b>Tip:</b> Cek kode task di halaman <b>My Tasks</b> dan kode checklist di detail task sebelum melakukan commit.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
