<div class="tab-pane fade" id="pills-network" role="tabpanel" aria-labelledby="pills-network-tab">
    <div class="card bg-dark border-0 shadow-lg">
        <div class="card-header bg-dark border-bottom border-secondary py-2">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="d-flex me-3">
                        <span class="rounded-circle bg-danger me-1" style="width: 10px; height: 10px;"></span>
                        <span class="rounded-circle bg-warning me-1" style="width: 10px; height: 10px;"></span>
                        <span class="rounded-circle bg-success" style="width: 10px; height: 10px;"></span>
                    </div>
                    <small class="text-light fw-bold">git-network-graph --all</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="p-3" style="overflow-x: auto; background-color: #0f1419; min-height: 400px;">
                @if($gitGraph)
                    <pre class="m-0" style="font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 14px; line-height: 1.5; color: #3dff6b; white-space: pre;">{{ $gitGraph }}</pre>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-terminal fa-3x text-secondary mb-3"></i>
                        <p class="text-secondary">No graph data available. Make some commits to see the flow.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="mt-3 p-3 bg-light rounded border">
        <h6 class="fw-bold small mb-2"><i class="fas fa-lightbulb text-warning me-1"></i> Cara Membaca Graph:</h6>
        <ul class="small text-muted mb-0 ps-3">
            <li>Garis vertikal/diagonal menunjukkan alur <b>Branching</b> dan <b>Merging</b>.</li>
            <li>Simbol <code>*</code> menunjukkan sebuah <b>Commit</b>.</li>
            <li>Teks dalam kurung <code>( )</code> menunjukkan lokasi <b>Branch</b> atau <b>Tag</b> saat ini.</li>
        </ul>
    </div>
</div>
