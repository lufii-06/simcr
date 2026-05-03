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
    </div>
</div>
