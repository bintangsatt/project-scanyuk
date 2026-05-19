@extends('layouts.app')

@section('title', 'Buat AR Project — Web AR Platform')

@push('styles')
<style>
    /* ===== FONTS ===== */
    @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap');

    :root {
        --primary: #6366f1;
        --primary-light: #818cf8;
        --primary-dark: #4f46e5;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --bg: #ffffff;
        --surface: #ffffff;
        --surface-2: #f8fafc;
        --border: #e6e6ea;
        --text: #0f172a;
        --text-muted: #6b7280;
        --radius: 14px;
    }

    body { background: var(--bg); color: var(--text); font-family: 'DM Sans', sans-serif; }

    /* Wizard Steps Header */
    .wiz-header {
        display: flex;
        align-items: center;
        gap: 0;
        padding: 1.5rem 2rem;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        margin-bottom: 1.5rem;
        overflow-x: auto;
    }
    .wiz-step {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        white-space: nowrap;
        transition: all .25s;
        font-family: 'Syne', sans-serif;
        font-weight: 600;
        font-size: .85rem;
        color: var(--text-muted);
        cursor: default;
    }
    .wiz-step .num {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--surface-2);
        border: 2px solid var(--border);
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem;
        transition: all .25s;
    }
    .wiz-step.active { color: var(--text); }
    .wiz-step.active .num { background: var(--primary); border-color: var(--primary); color: #fff; }
    .wiz-step.done .num { background: var(--success); border-color: var(--success); color: #fff; }
    .wiz-step.done { color: var(--success); }
    .wiz-connector {
        flex: 1; min-width: 20px; max-width: 60px;
        height: 2px; background: var(--border); margin: 0 4px;
        transition: background .4s;
    }
    .wiz-connector.done { background: var(--success); }

    /* Cards */
    .ar-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }
    .ar-card .card-head {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid var(--border);
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        display: flex; align-items: center; gap: .5rem;
        color: var(--text);
    }
    .ar-card .card-head .icon { color: var(--primary); }
    .ar-card .card-body-ar { padding: 1.75rem; }

    /* Steps */
    .step-panel { display: none; }
    .step-panel.active { display: block; }

    /* Drop zone */
    .drop-zone {
        border: 2px dashed var(--border);
        border-radius: var(--radius);
        padding: 3rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        background: var(--surface-2);
    }
    .drop-zone:hover, .drop-zone.drag-over {
        border-color: var(--primary);
        background: rgba(99,102,241,.07);
    }
    .drop-zone .dz-icon { font-size: 2.5rem; color: var(--text-muted); margin-bottom: .75rem; }

    /* Progress bar */
    .prog-wrap {
        background: var(--surface-2);
        border-radius: 99px;
        height: 8px;
        overflow: hidden;
    }
    .prog-bar {
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
        transition: width .6s ease;
    }
    .prog-bar.success { background: linear-gradient(90deg, var(--success), #34d399); }
    .prog-bar.indeterminate {
        width: 40% !important;
        animation: slide-prog 1.4s ease-in-out infinite;
    }
    @keyframes slide-prog {
        0% { transform: translateX(-150%); }
        100% { transform: translateX(350%); }
    }

    /* Status badges */
    .badge-status {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .35rem .75rem; border-radius: 99px;
        font-size: .78rem; font-weight: 500;
    }
    .badge-status .dot {
        width: 7px; height: 7px; border-radius: 50%;
    }
    .badge-status.processing { background: rgba(245,158,11,.12); color: var(--warning); }
    .badge-status.processing .dot { background: var(--warning); animation: blink 1.2s ease infinite; }
    .badge-status.ready { background: rgba(16,185,129,.12); color: var(--success); }
    .badge-status.ready .dot { background: var(--success); }
    .badge-status.failed { background: rgba(239,68,68,.12); color: var(--danger); }
    .badge-status.failed .dot { background: var(--danger); }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

    /* Tabs for mode */
    .mode-tabs { display: flex; gap: .5rem; margin-bottom: 1.5rem; }
    .mode-tab {
        padding: .5rem 1.25rem; border-radius: 8px; border: 1px solid var(--border);
        background: var(--surface-2); color: var(--text-muted);
        cursor: pointer; font-size: .87rem; font-weight: 500;
        transition: all .2s;
    }
    .mode-tab:hover { border-color: var(--primary); color: var(--text); }
    .mode-tab.active { background: var(--primary); border-color: var(--primary); color: #fff; }

    /* Template grid */
    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: .875rem;
    }
    .tpl-card {
        background: var(--surface-2); border: 2px solid var(--border);
        border-radius: 10px; padding: .75rem; cursor: pointer;
        transition: all .2s; text-align: center;
    }
    .tpl-card:hover { border-color: var(--primary-light); }
    .tpl-card.selected { border-color: var(--primary); background: rgba(99,102,241,.1); }
    .tpl-card .tpl-thumb {
        height: 90px; border-radius: 6px;
        background: var(--surface); margin-bottom: .5rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: var(--text-muted);
    }
    .tpl-card .tpl-name { font-size: .8rem; color: var(--text-muted); }
    .tpl-card.selected .tpl-name { color: var(--text); }

    /* 3D Preview canvas */
    .canvas-wrap {
        background: #0a0a10; /* dark only for canvas area */
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }
    .canvas-wrap canvas {
        width: 100% !important; height: 100% !important; display: block;
    }
    #canvas-3d { width: 100%; height: 380px; }

    .marker-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: .75rem;
        margin-bottom: 1rem;
    }
    .marker-card {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 14px;
        cursor: pointer;
        overflow: hidden;
        transition: all .2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .5rem;
        padding: .75rem;
    }
    .marker-card:hover {
        border-color: var(--primary);
    }
    .marker-card.selected {
        border-color: var(--primary);
        background: rgba(99,102,241,.12);
    }
    .marker-card img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,.08);
    }
    .marker-card .marker-name {
        font-size: .82rem;
        color: var(--text);
        text-align: center;
        width: 100%;
    }

    /* Transform controls panel */
    .transform-panel {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 1rem 1.25rem;
    }
    .transform-panel h6 {
        font-family: 'Syne', sans-serif; font-size: .8rem;
        font-weight: 700; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: .06em;
        margin-bottom: .75rem;
    }
    .xyz-inputs { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: .5rem; }
    .xyz-input-wrap { display: flex; flex-direction: column; gap: .3rem; }
    .xyz-label {
        font-size: .72rem; font-weight: 700;
        text-align: center; border-radius: 4px;
        padding: 1px 0;
    }
    .xyz-label.x { background: rgba(239,68,68,.2); color: #f87171; }
    .xyz-label.y { background: rgba(34,197,94,.2); color: #4ade80; }
    .xyz-label.z { background: rgba(59,130,246,.2); color: #60a5fa; }
    .xyz-num {
        background: var(--bg); border: 1px solid var(--border);
        border-radius: 6px; padding: .35rem .5rem;
        color: var(--text); font-size: .85rem;
        text-align: center; width: 100%;
    }
    .xyz-num:focus { outline: none; border-color: var(--primary); }

    /* Scale range */
    .scale-range { accent-color: var(--primary); width: 100%; }

    /* Action buttons */
    .btn-ar-primary {
        background: var(--primary); color: #fff;
        border: none; border-radius: 8px;
        padding: .6rem 1.75rem; font-size: .9rem; font-weight: 600;
        font-family: 'Syne', sans-serif;
        cursor: pointer; transition: all .2s;
        display: inline-flex; align-items: center; gap: .4rem;
    }
    .btn-ar-primary:hover { background: var(--primary-dark); }
    .btn-ar-primary:disabled { opacity: .45; cursor: not-allowed; }
    .btn-ar-primary.success-btn { background: var(--success); }
    .btn-ar-primary.success-btn:hover { background: #059669; }

    .btn-ar-ghost {
        background: transparent; color: var(--text-muted);
        border: 1px solid var(--border); border-radius: 8px;
        padding: .6rem 1.25rem; font-size: .9rem; font-weight: 500;
        cursor: pointer; transition: all .2s;
        display: inline-flex; align-items: center; gap: .4rem;
    }
    .btn-ar-ghost:hover { border-color: var(--text-muted); color: var(--text); }

    .step-nav { display: flex; justify-content: space-between; align-items: center; margin-top: 1.75rem; }

    /* Form input dark */
    .ar-input {
        background: var(--surface-2); border: 1px solid var(--border);
        border-radius: 8px; padding: .5rem .875rem;
        color: var(--text); font-size: .9rem; width: 100%;
    }
    .ar-input:focus { outline: none; border-color: var(--primary); }
    .ar-label { font-size: .82rem; color: var(--text-muted); margin-bottom: .35rem; display: block; }

    /* Blend processing status */
    #blend-job-status { transition: all .3s; }

    /* Drag handle indicator in preview */
    .canvas-hint {
        position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);
        background: rgba(0,0,0,.6); color: rgba(255,255,255,.6);
        font-size: .72rem; padding: .25rem .75rem; border-radius: 99px;
        pointer-events: none;
    }

    /* Marker img preview */
    #marker-img-preview {
        max-height: 180px; width: auto; max-width: 100%;
        border-radius: 10px; border: 1px solid var(--border);
    }

    /* Review summary */
    .review-row { display: flex; gap: .5rem; align-items: flex-start; margin-bottom: .75rem; }
    .review-key { font-size: .8rem; color: var(--text-muted); min-width: 90px; }
    .review-val { font-size: .88rem; color: var(--text); font-weight: 500; }

    /* Fade-in */
    .step-panel.active { animation: fadein .3s ease; }
    @keyframes fadein { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }

    /* scrollbar dark */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-xl-10 col-lg-11">

    {{-- ===== WIZARD HEADER ===== --}}
    <div class="wiz-header">
        <div class="wiz-step active" id="wiz-1">
            <span class="num">1</span> Upload Marker
        </div>
        <div class="wiz-connector" id="conn-1"></div>
        <div class="wiz-step" id="wiz-2">
            <span class="num">2</span> Pilih Konten AR
        </div>
        <div class="wiz-connector" id="conn-2"></div>
        <div class="wiz-step" id="wiz-3">
            <span class="num">3</span> Preview & Posisi
        </div>
        <div class="wiz-connector" id="conn-3"></div>
        <div class="wiz-step" id="wiz-4">
            <span class="num">4</span> Generate AR
        </div>
    </div>

    {{-- ===== STEP 1: Upload Gambar Marker ===== --}}
    <div class="step-panel active" id="step-1">
        <div class="ar-card">
            <div class="card-head">
                <i class="bi bi-image icon"></i>
                Step 1 — Upload Gambar Marker
            </div>
            <div class="card-body-ar">
                <p class="text-muted small mb-3">Upload gambar yang akan dijadikan marker AR. Gambar dengan banyak detail dan kontras tinggi menghasilkan tracking lebih baik.</p>

                <div class="marker-library mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold">Marker tersedia</div>
                        <button type="button" class="btn-ar-ghost" onclick="resetMarkerUpload()" style="padding:.35rem .75rem;font-size:.82rem">
                            <i class="bi bi-upload"></i> Upload marker sendiri
                        </button>
                    </div>
                    <div id="marker-grid" class="marker-grid">
                        <div class="text-muted small"><div class="spinner-border spinner-border-sm me-2"></div>Memuat marker siap pakai...</div>
                    </div>
                </div>

                {{-- Drop zone --}}
                <div class="drop-zone" id="marker-drop-zone">
                    <input type="file" id="marker-file-input" accept=".jpg,.jpeg,.png" class="d-none">
                    <div class="dz-icon"><i class="bi bi-image-fill"></i></div>
                    <p class="fw-semibold mb-2" style="color:var(--text)">Drag & drop gambar di sini</p>
                    <p class="text-muted small mb-3">JPG, PNG — max 10MB</p>
                    <button type="button" class="btn-ar-primary" onclick="document.getElementById('marker-file-input').click()">
                        <i class="bi bi-folder2-open"></i> Pilih File
                    </button>
                </div>

                {{-- After upload --}}
                <div id="marker-after-upload" class="d-none mt-3">
                    <div class="row g-4 align-items-start">
                        <div class="col-md-4 text-center">
                            <img id="marker-img-preview" src="" alt="Marker preview">
                            <p id="marker-fname" class="mt-2 small text-muted mb-0"></p>
                            <button type="button" class="btn-ar-ghost mt-3" style="padding:.35rem .75rem;font-size:.82rem" onclick="resetMarkerUpload()">
                                <i class="bi bi-arrow-counterclockwise"></i> Pilih marker lain
                            </button>
                        </div>
                        <div class="col-md-8">
                            <h6 class="mb-1" style="font-family:Syne,sans-serif;font-weight:700">Konversi Marker</h6>
                            <p class="small text-muted mb-3">Gambar akan diubah menjadi file <code>.mind</code> untuk tracking AR.</p>

                            {{-- Status --}}
                            <div id="mstatus-uploading" class="d-none mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                    <span class="small">Mengupload...</span>
                                </div>
                            </div>

                            <div id="mstatus-processing" class="d-none mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge-status processing"><span class="dot"></span> Memproses marker...</span>
                                </div>
                                <div class="prog-wrap">
                                    <div class="prog-bar indeterminate" id="marker-progbar" style="width:40%"></div>
                                </div>
                                <p class="small text-muted mt-2">Sedang mengcompile file .mind. Mohon tunggu...</p>
                            </div>

                            <div id="mstatus-ready" class="d-none mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge-status ready"><span class="dot"></span> Marker siap digunakan!</span>
                                </div>
                                <div class="prog-wrap">
                                    <div class="prog-bar success" style="width:100%"></div>
                                </div>
                            </div>

                            <div id="mstatus-failed" class="d-none mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge-status failed"><span class="dot"></span> Gagal memproses marker</span>
                                </div>
                                <button type="button" class="btn-ar-ghost mt-2" onclick="resetMarkerUpload()">
                                    <i class="bi bi-arrow-counterclockwise"></i> Coba Lagi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="step-nav">
                    <span></span>
                    <button id="btn-next-1" class="btn-ar-primary" disabled onclick="goToStep(2)">
                        Lanjut <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== STEP 2: Pilih Konten AR ===== --}}
    <div class="step-panel" id="step-2">
        <div class="ar-card">
            <div class="card-head">
                <i class="bi bi-collection icon"></i>
                Step 2 — Pilih Konten AR
            </div>
            <div class="card-body-ar">
                <div class="mode-tabs">
                    <button class="mode-tab active" id="tab-template" onclick="switchMode('template')">
                        <i class="bi bi-grid-1x2 me-1"></i> Template
                    </button>
                    <button class="mode-tab" id="tab-gltf" onclick="switchMode('gltf')">
                        <i class="bi bi-cube me-1"></i> GLB / GLTF
                    </button>
                    <button class="mode-tab" id="tab-blend" onclick="switchMode('blend')">
                        <i class="bi bi-file-earmark me-1"></i> Blend
                    </button>
                </div>

                {{-- MODE: Template --}}
                <div id="mode-template">
                    <div class="template-grid" id="template-grid">
                        <div class="text-muted small"><div class="spinner-border spinner-border-sm me-2"></div>Memuat template...</div>
                    </div>
                    <div id="tpl-config-area" class="d-none mt-4">
                        <hr style="border-color:var(--border)">
                        <h6 style="font-family:Syne,sans-serif;font-weight:700" class="mb-3">Konfigurasi Template</h6>
                        <div id="tpl-config-fields"></div>
                    </div>
                </div>

                {{-- MODE: GLB/GLTF --}}
                <div id="mode-gltf" class="d-none">
                    <div class="drop-zone" id="gltf-drop-zone">
                        <input type="file" id="gltf-file-input" accept=".glb,.gltf" class="d-none">
                        <div class="dz-icon"><i class="bi bi-cube"></i></div>
                        <p class="fw-semibold mb-1" style="color:var(--text)">Upload file .glb atau .gltf</p>
                        <p class="text-muted small mb-3">Max 20MB. Animasi dan material didukung penuh.</p>
                        <button type="button" class="btn-ar-primary" onclick="document.getElementById('gltf-file-input').click()">
                            <i class="bi bi-folder2-open"></i> Pilih File
                        </button>
                    </div>
                    <div id="gltf-chosen" class="d-none mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span id="gltf-fname" class="small fw-semibold"></span>
                            </div>
                            <button class="btn-ar-ghost" style="padding:.35rem .75rem;font-size:.8rem" onclick="resetGltf()">
                                <i class="bi bi-x"></i> Ganti
                            </button>
                        </div>
                        <div class="badge-status ready" style="display:inline-flex"><span class="dot"></span> File siap</div>
                    </div>
                </div>

                {{-- MODE: Blend --}}
                <div id="mode-blend" class="d-none">
                    <div class="drop-zone" id="blend-drop-zone">
                        <input type="file" id="blend-file-input" accept=".blend" class="d-none">
                        <div class="dz-icon"><i class="bi bi-file-earmark-code"></i></div>
                        <p class="fw-semibold mb-1" style="color:var(--text)">Upload file .blend</p>
                        <p class="text-muted small mb-3">File akan dikonversi ke .glb di server menggunakan Blender CLI.</p>
                    <div class="p-3 mb-3 rounded" style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3)">
                        <p class="small mb-1" style="color:#f59e0b"><i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Penting sebelum upload:</strong></p>
                        <p class="small mb-0" style="color:#cbd5e1">Pastikan semua texture sudah di-<strong>pack</strong> ke dalam file .blend:<br>
                        Di Blender → <code>File → External Data → Pack All Into .blend</code><br>
                        Tanpa ini, warna/texture tidak akan muncul di hasil AR.</p>
                    </div>
                        <button type="button" class="btn-ar-primary" onclick="document.getElementById('blend-file-input').click()">
                            <i class="bi bi-folder2-open"></i> Pilih File
                        </button>
                    </div>

                    {{-- Blend processing status --}}
                    <div id="blend-after-upload" class="d-none mt-3">
                        <div id="blend-uploading" class="d-none mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="spinner-border spinner-border-sm text-primary"></div>
                                <span class="small">Mengupload dan memulai konversi...</span>
                            </div>
                        </div>
                        <div id="blend-processing" class="d-none mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge-status processing"><span class="dot"></span> Mengkonversi .blend → .glb...</span>
                            </div>
                            <div class="prog-wrap">
                                <div class="prog-bar indeterminate" style="width:40%"></div>
                            </div>
                            <p class="small text-muted mt-2">Proses ini bisa memakan waktu 1–3 menit tergantung kompleksitas file.</p>
                        </div>
                        <div id="blend-done" class="d-none mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge-status ready"><span class="dot"></span> Konversi selesai!</span>
                            </div>
                            <div class="prog-wrap">
                                <div class="prog-bar success" style="width:100%"></div>
                            </div>
                        </div>
                        <div id="blend-failed" class="d-none">
                            <span class="badge-status failed"><span class="dot"></span> Konversi gagal</span>
                            <p id="blend-error-msg" class="small text-danger mt-1 mb-0"></p>
                            <button class="btn-ar-ghost mt-2" style="padding:.35rem .75rem;font-size:.8rem" onclick="resetBlend()">
                                <i class="bi bi-arrow-counterclockwise"></i> Coba Lagi
                            </button>
                        </div>
                    </div>
                </div>

                <div class="step-nav">
                    <button class="btn-ar-ghost" onclick="goToStep(1)">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </button>
                    <button id="btn-next-2" class="btn-ar-primary" disabled onclick="goToStep(3)">
                        Lanjut <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== STEP 3: Preview & Posisi ===== --}}
    <div class="step-panel" id="step-3">
        <div class="ar-card">
            <div class="card-head">
                <i class="bi bi-arrows-move icon"></i>
                Step 3 — Preview 3D & Atur Posisi
            </div>
            <div class="card-body-ar">
                <p class="small text-muted mb-3">Geser, putar, dan atur ukuran model langsung di preview. Angka di form akan ikut berubah secara real-time.</p>

                <div class="row g-4">
                    {{-- Preview canvas --}}
                    <div class="col-lg-7">
                        <div class="canvas-wrap" style="height:400px; position:relative">
                            <canvas id="canvas-3d"></canvas>
                            <div class="canvas-hint">Drag rotate &nbsp;·&nbsp; Scroll zoom</div>
                            <div id="canvas-loading" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(10,10,16,.8)">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-2"></div>
                                    <p class="small text-muted mb-0">Memuat model 3D...</p>
                                </div>
                            </div>
                        </div>
                        {{-- Scale slider below canvas --}}
                        <div class="transform-panel mt-3">
                            <h6>UKURAN (SCALE)</h6>
                            <div class="d-flex align-items-center gap-3">
                                <input type="range" class="scale-range" id="scale-slider" min="0.05" max="5" step="0.05" value="1">
                                <span id="scale-display" style="min-width:40px;text-align:right;font-size:.9rem;font-weight:600;font-family:Syne,sans-serif;color:var(--primary)">1.00</span>
                            </div>
                        </div>
                    </div>

                    {{-- Controls --}}
                    <div class="col-lg-5 d-flex flex-column gap-3">
                        {{-- Position --}}
                        <div class="transform-panel">
                            <h6>POSISI (POSITION)</h6>
                            <div class="xyz-inputs">
                                <div class="xyz-input-wrap">
                                    <span class="xyz-label x">X</span>
                                    <input type="number" class="xyz-num" id="pos-x" value="0" step="0.1" oninput="applyTransformFromForm()">
                                </div>
                                <div class="xyz-input-wrap">
                                    <span class="xyz-label y">Y</span>
                                    <input type="number" class="xyz-num" id="pos-y" value="0" step="0.1" oninput="applyTransformFromForm()">
                                </div>
                                <div class="xyz-input-wrap">
                                    <span class="xyz-label z">Z</span>
                                    <input type="number" class="xyz-num" id="pos-z" value="0" step="0.1" oninput="applyTransformFromForm()">
                                </div>
                            </div>
                        </div>

                        {{-- Rotation --}}
                        <div class="transform-panel">
                            <h6>ROTASI (DEGREES)</h6>
                            <div class="xyz-inputs">
                                <div class="xyz-input-wrap">
                                    <span class="xyz-label x">X</span>
                                    <input type="number" class="xyz-num" id="rot-x" value="0" step="1" oninput="applyTransformFromForm()">
                                </div>
                                <div class="xyz-input-wrap">
                                    <span class="xyz-label y">Y</span>
                                    <input type="number" class="xyz-num" id="rot-y" value="0" step="1" oninput="applyTransformFromForm()">
                                </div>
                                <div class="xyz-input-wrap">
                                    <span class="xyz-label z">Z</span>
                                    <input type="number" class="xyz-num" id="rot-z" value="0" step="1" oninput="applyTransformFromForm()">
                                </div>
                            </div>
                        </div>

                        {{-- Marker thumb --}}
                        <div class="transform-panel">
                            <h6>MARKER AKTIF</h6>
                            <img id="preview-marker-thumb" src="" class="img-fluid rounded" style="max-height:100px;width:auto">
                        </div>

                        {{-- Orbit animation panel --}}
                        <div class="transform-panel" id="orbit-panel">
                            <h6>ORBIT MENGELILINGI MARKER</h6>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <button id="btn-orbit" class="btn-ar-ghost" style="padding:.35rem .9rem;font-size:.82rem;flex:1" onclick="toggleOrbit()">
                                    <i class="bi bi-play-circle" id="orbit-icon"></i> Mulai Orbit
                                </button>
                                <button class="btn-ar-ghost" style="padding:.35rem .7rem;font-size:.82rem" onclick="toggleOrbitDir()" title="Balik arah">
                                    <i class="bi bi-arrow-repeat" id="orbit-dir-icon"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted" style="min-width:40px">Speed</span>
                                <input type="range" class="scale-range" id="orbit-speed" min="0.1" max="3" step="0.1" value="0.5" style="flex:1">
                                <span id="orbit-speed-val" class="small" style="min-width:30px;text-align:right;color:var(--primary)">0.5×</span>
                            </div>
                            {{-- Orbit radius --}}
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span class="small text-muted" style="min-width:40px">Radius</span>
                                <input type="range" class="scale-range" id="orbit-radius" min="0.5" max="4" step="0.1" value="1.5" style="flex:1">
                                <span id="orbit-radius-val" class="small" style="min-width:30px;text-align:right;color:var(--primary)">1.5</span>
                            </div>
                        </div>

                        {{-- Animation clip selector --}}
                        <div class="transform-panel" id="anim-clip-panel" style="display:none">
                            <h6>ANIMASI CLIP</h6>
                            <select id="anim-clip-select" class="ar-input" style="padding:.4rem .7rem;font-size:.82rem" onchange="switchAnimClip(this.value)">
                            </select>
                        </div>

                        {{-- Reset button --}}
                        <button class="btn-ar-ghost" onclick="resetTransform()">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset Posisi
                        </button>
                    </div>
                </div>

                <div class="step-nav">
                    <button class="btn-ar-ghost" onclick="goToStep(2)">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </button>
                    <button class="btn-ar-primary" onclick="goToStep(4)">
                        Lanjut <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== STEP 4: Generate AR ===== --}}
    <div class="step-panel" id="step-4">
        <div class="ar-card">
            <div class="card-head">
                <i class="bi bi-qr-code icon"></i>
                Step 4 — Review & Generate AR
            </div>
            <div class="card-body-ar">
                <div class="row g-4 mb-4">
                    {{-- Marker thumb --}}
                    <div class="col-md-3">
                        <p class="ar-label">Marker</p>
                        <img id="gen-marker-img" src="" class="img-fluid rounded" style="border:1px solid var(--border)">
                    </div>
                    {{-- Summary --}}
                    <div class="col-md-9">
                        <p class="ar-label">Ringkasan Project</p>
                        <div id="review-summary">
                            <div class="review-row">
                                <span class="review-key">Tipe Konten</span>
                                <span class="review-val" id="gen-type">—</span>
                            </div>
                            <div class="review-row">
                                <span class="review-key">Model / Template</span>
                                <span class="review-val" id="gen-model">—</span>
                            </div>
                            <div class="review-row">
                                <span class="review-key">Scale</span>
                                <span class="review-val" id="gen-scale">1.00</span>
                            </div>
                            <div class="review-row">
                                <span class="review-key">Position</span>
                                <span class="review-val" id="gen-position">X: 0, Y: 0, Z: 0</span>
                            </div>
                            <div class="review-row">
                                <span class="review-key">Rotation</span>
                                <span class="review-val" id="gen-rotation">X: 0°, Y: 0°, Z: 0°</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hidden form --}}
                <form id="generate-form" action="{{ route('ar.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="marker_id"   id="form-marker-id">
                    <input type="hidden" name="type"        id="form-type">
                    <input type="hidden" name="template_id" id="form-template-id">
                    <input type="hidden" name="scale"        id="form-scale"        value="1">
                    <input type="hidden" name="position"     id="form-position"     value="[0,0,0]">
                    <input type="hidden" name="rotation"     id="form-rotation"     value="[0,0,0]">
                    <input type="hidden" name="orbit_active" id="form-orbit-active" value="0">
                    <input type="hidden" name="orbit_speed"  id="form-orbit-speed"  value="0.5">
                    <input type="hidden" name="orbit_radius" id="form-orbit-radius" value="1.5">
                    <input type="hidden" name="orbit_dir"    id="form-orbit-dir"    value="1">
                    <input type="hidden" name="anim_clip"    id="form-anim-clip"    value="*">
                    <div id="form-config-fields"></div>
                    <input type="file"   id="form-model-file" name="model" class="d-none">
                    {{-- For blend: server-side project_id after async conversion --}}
                    <input type="hidden" name="blend_project_id" id="form-blend-project-id">
                </form>

                <div class="step-nav">
                    <button class="btn-ar-ghost" onclick="goToStep(3)">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </button>
                    <button id="btn-generate" class="btn-ar-primary success-btn btn-lg" onclick="submitGenerate()">
                        <i class="bi bi-magic"></i> Generate AR!
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /col --}}
</div>{{-- /row --}}
@endsection

@push('scripts')
<script async src="https://unpkg.com/es-module-shims@1.8.0/dist/es-module-shims.js"></script>
<script type="importmap">
{
  "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
  }
}
</script>

<script type="module">
import * as THREE from 'three';
import { GLTFLoader }    from 'three/addons/loaders/GLTFLoader.js';
import { DRACOLoader }   from 'three/addons/loaders/DRACOLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

// ─── STATE ───────────────────────────────────────────────────────────────────
const state = {
    step: 1,
    // Marker
    markerId: null,
    markerStatus: null,
    markerImageUrl: null,
    markerPollingTimer: null,
    // Mode
    mode: 'template',
    // Template
    selectedTemplateId: null,
    selectedTemplateName: null,
    selectedTemplateUrl: null,
    templateConfig: {},
    // GLTF
    gltfFile: null,
    gltfBlob: null,
    // Blend
    blendProjectId: null,   // server-created project id after async conversion
    blendGlbUrl: null,       // GLB url returned after conversion
    blendPollingTimer: null,
    blendStatus: null,       // null | uploading | processing | done | failed
    // Transform
    scale: 1.0,
    position: [0, 0, 0],
    rotation: [0, 0, 0],
};

// ─── THREE.JS ─────────────────────────────────────────────────────────────────
let renderer, scene, camera, orbitControls, mixer, clock, animFrame;
let previewModel = null;
let markerPlane  = null;  // marker sebagai objek 3D di scene
let pivotGroup   = null;  // group untuk orbit: model dimasukkan ke sini
let allClips     = [];    // semua AnimationClip dari GLB
let activeAction = null;  // AnimationAction yang sedang play

// Orbit state
const orbitState = {
    active:  false,
    speed:   0.5,    // rad/s
    dir:     1,      // 1 = CW, -1 = CCW
    radius:  1.5,
    angle:   0,
};

function initThree() {
    const canvas = document.getElementById('canvas-3d');
    const wrap = canvas.parentElement;

    // Ambil ukuran nyata; offsetWidth lebih reliable saat elemen baru visible
    const W = wrap.offsetWidth  || wrap.clientWidth  || 600;
    const H = wrap.offsetHeight || wrap.clientHeight || 400;

    // Jika renderer sudah ada (user balik ke step 3), cukup resize — jangan init ulang
    if (renderer) {
        renderer.setSize(W, H);
        camera.aspect = W / H;
        camera.updateProjectionMatrix();
        return;
    }

    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setSize(W, H);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    // SRGBColorSpace wajib agar warna GLB Blender tampil benar
    renderer.outputColorSpace  = THREE.SRGBColorSpace;
    // LinearToneMapping = tidak ada perubahan warna — paling akurat untuk preview
    renderer.toneMapping       = THREE.LinearToneMapping;
    renderer.toneMappingExposure = 1.0;

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0a0a10);

    camera = new THREE.PerspectiveCamera(45, W / H, 0.01, 500);
    camera.position.set(0, 1.5, 4);

    // Lights
    const amb = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(amb);
    const dir = new THREE.DirectionalLight(0xffffff, 1.2);
    dir.position.set(5, 8, 5);
    scene.add(dir);
    const fill = new THREE.DirectionalLight(0x8899ff, 0.4);
    fill.position.set(-5, 2, -5);
    scene.add(fill);

    // Grid tipis sebagai lantai referensi
    const grid = new THREE.GridHelper(6, 12, 0x222233, 0x1a1a28);
    scene.add(grid);

    orbitControls = new OrbitControls(camera, renderer.domElement);
    orbitControls.enableDamping = true;
    orbitControls.dampingFactor = 0.08;

    // Listen for orbit changes to update form fields
    orbitControls.addEventListener('change', () => {
        if (previewModel) syncFormFromModel();
    });

    clock = new THREE.Clock();

    function animate() {
        animFrame = requestAnimationFrame(animate);
        const delta = clock.getDelta();

        if (mixer) mixer.update(delta);

        // Orbit: putar pivotGroup mengelilingi Y axis (di atas marker)
        if (orbitState.active && pivotGroup) {
            orbitState.angle += orbitState.speed * orbitState.dir * delta;

            // Posisi model pada lingkaran orbit
            const r = orbitState.radius;
            pivotGroup.position.x = Math.sin(orbitState.angle) * r;
            pivotGroup.position.z = Math.cos(orbitState.angle) * r;

            // Model selalu menghadap ke tengah marker (pusat orbit)
            pivotGroup.rotation.y = orbitState.angle;
        }

        orbitControls.update();
        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', () => {
        const W2 = wrap.clientWidth, H2 = wrap.clientHeight;
        camera.aspect = W2 / H2;
        camera.updateProjectionMatrix();
        renderer.setSize(W2, H2);
    });
}

// DRACOLoader shared instance (Blender sering export dengan Draco compression)
const dracoLoader = new DRACOLoader();
dracoLoader.setDecoderPath('https://unpkg.com/three@0.160.0/examples/jsm/libs/draco/gltf/');

function loadModelIntoPreview(url) {
    document.getElementById('canvas-loading').style.display = 'flex';

    // Remove old model
    if (previewModel) { scene.remove(previewModel); previewModel = null; }
    if (mixer) { mixer.stopAllAction(); mixer = null; }

    const loader = new GLTFLoader();
    loader.setDRACOLoader(dracoLoader); // handle Draco-compressed GLB

    loader.load(url, (gltf) => {
        previewModel = gltf.scene;

        // Fix material dari GLB Blender
        previewModel.traverse((node) => {
            if (!node.isMesh) return;
            node.castShadow    = true;
            node.receiveShadow = true;

            const mats = Array.isArray(node.material) ? node.material : [node.material];
            mats.forEach(mat => {
                if (!mat) return;

                // Pastikan material flag benar
                mat.side = THREE.FrontSide;

                // Vertex colors
                if (node.geometry?.attributes?.color) {
                    mat.vertexColors = true;
                }

                mat.needsUpdate = true;
            });
        });

        // Normalize ukuran ke ~1.2 unit
        const box    = new THREE.Box3().setFromObject(previewModel);
        const center = box.getCenter(new THREE.Vector3());
        const size   = box.getSize(new THREE.Vector3());
        const maxDim = Math.max(size.x, size.y, size.z) || 1;
        const norm   = 1.2 / maxDim;

        previewModel.scale.setScalar(norm);

        // Center XZ, tapi Y: taruh bagian bawah model tepat di Y=0 (di atas marker)
        const bottomY = box.min.y * norm;
        previewModel.position.set(
            -center.x * norm,
            -bottomY,           // angkat agar kaki model menyentuh marker
            -center.z * norm
        );

        // Simpan base scale agar slider scale relatif terhadap ini
        previewModel.userData._baseScale = norm;
        previewModel.userData._bottomY   = -bottomY;

        // Reset orbit state saat model baru dimuat
        orbitState.active = false;
        orbitState.angle  = 0;
        const orbitBtn = document.getElementById('btn-orbit');
        if (orbitBtn) {
            orbitBtn.innerHTML   = '<i class="bi bi-play-circle" id="orbit-icon"></i> Mulai Orbit';
            orbitBtn.style.borderColor = '';
            orbitBtn.style.color = '';
        }

        // Wrap model dalam pivotGroup agar orbit tidak konflik dengan posisi model
        if (pivotGroup) scene.remove(pivotGroup);
        pivotGroup = new THREE.Group();
        pivotGroup.add(previewModel);
        scene.add(pivotGroup);

        // Animasi
        allClips = gltf.animations;
        if (allClips.length > 0) {
            mixer = new THREE.AnimationMixer(previewModel);

            // Play clip pertama secara default
            activeAction = mixer.clipAction(allClips[0]);
            activeAction.play();

            // Populate clip selector
            const select = document.getElementById('anim-clip-select');
            select.innerHTML = allClips.map((clip, i) =>
                `<option value="${i}">${clip.name}</option>`
            ).join('');
            document.getElementById('anim-clip-panel').style.display = '';
        } else {
            document.getElementById('anim-clip-panel').style.display = 'none';
        }

        applyTransformToModel();
        document.getElementById('canvas-loading').style.display = 'none';
    }, undefined, (err) => {
        console.error('GLB load error:', err);
        document.getElementById('canvas-loading').innerHTML =
            '<p class="text-danger small px-3">Gagal memuat model 3D.<br>Pastikan file GLB valid.</p>';
    });
}

/** Apply form values → 3D model */
window.applyTransformFromForm = () => {
    state.position = [
        parseFloat(document.getElementById('pos-x').value) || 0,
        parseFloat(document.getElementById('pos-y').value) || 0,
        parseFloat(document.getElementById('pos-z').value) || 0,
    ];
    state.rotation = [
        parseFloat(document.getElementById('rot-x').value) || 0,
        parseFloat(document.getElementById('rot-y').value) || 0,
        parseFloat(document.getElementById('rot-z').value) || 0,
    ];
    applyTransformToModel();
};

function applyTransformToModel() {
    if (!previewModel) return;
    const baseY = previewModel.userData._bottomY || 0;

    // Posisi & rotasi pada previewModel (lokal di dalam pivotGroup)
    previewModel.position.set(
        state.position[0],
        state.position[1] + baseY,
        state.position[2]
    );
    previewModel.rotation.set(
        THREE.MathUtils.degToRad(state.rotation[0]),
        THREE.MathUtils.degToRad(state.rotation[1]),
        THREE.MathUtils.degToRad(state.rotation[2])
    );
    const base = previewModel.userData._baseScale || 1;
    previewModel.scale.setScalar(state.scale * base);

    // Saat tidak orbit: pivotGroup ikut posisi state
    if (!orbitState.active && pivotGroup) {
        pivotGroup.position.set(0, 0, 0);
        pivotGroup.rotation.set(0, 0, 0);
    }
}

/** Read 3D model transform → update form fields */
function syncFormFromModel() {
    if (!previewModel) return;
    const p      = previewModel.position;
    const bottomY = previewModel.userData._bottomY || 0;

    document.getElementById('pos-x').value = p.x.toFixed(2);
    document.getElementById('pos-y').value = (p.y - bottomY).toFixed(2);
    document.getElementById('pos-z').value = p.z.toFixed(2);

    const r = previewModel.rotation;
    document.getElementById('rot-x').value = THREE.MathUtils.radToDeg(r.x).toFixed(1);
    document.getElementById('rot-y').value = THREE.MathUtils.radToDeg(r.y).toFixed(1);
    document.getElementById('rot-z').value = THREE.MathUtils.radToDeg(r.z).toFixed(1);
}

window.resetTransform = () => {
    state.position = [0, 0, 0];
    state.rotation = [0, 0, 0];
    state.scale = 1;
    document.getElementById('pos-x').value = 0;
    document.getElementById('pos-y').value = 0;
    document.getElementById('pos-z').value = 0;
    document.getElementById('rot-x').value = 0;
    document.getElementById('rot-y').value = 0;
    document.getElementById('rot-z').value = 0;
    document.getElementById('scale-slider').value = 1;
    document.getElementById('scale-display').textContent = '1.00';
    applyTransformToModel();
};

// Scale slider
document.getElementById('scale-slider').addEventListener('input', function () {
    state.scale = parseFloat(this.value);
    document.getElementById('scale-display').textContent = state.scale.toFixed(2);
    applyTransformToModel();
});

// ─── STEP NAVIGATION ─────────────────────────────────────────────────────────
window.goToStep = (target) => {
    // Guards
    if (target === 2 && !state.markerId) return;
    if (target === 3 && !isStep2Complete()) return;
    if (target === 4) populateGenerateReview();

    // Tampilkan panel DULU agar canvas punya clientWidth/clientHeight nyata
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.getElementById(`step-${target}`).classList.add('active');

    // Update wizard header
    for (let i = 1; i <= 4; i++) {
        const el = document.getElementById(`wiz-${i}`);
        el.classList.remove('active', 'done');
        if (i < target) el.classList.add('done'), el.querySelector('.num').innerHTML = '<i class="bi bi-check-lg" style="font-size:.75rem"></i>';
        else if (i === target) el.classList.add('active'), el.querySelector('.num').textContent = i;
        else el.querySelector('.num').textContent = i;

        if (i < 4) {
            const conn = document.getElementById(`conn-${i}`);
            conn.classList.toggle('done', i < target);
        }
    }
    state.step = target;

    // Step 3: init/resize Three.js SETELAH panel visible + browser reflow selesai
    // requestAnimationFrame memastikan canvas sudah punya clientWidth/clientHeight nyata
    if (target === 3) {
        requestAnimationFrame(() => {
            enterStep3();
        });
    }
};

// ─── STEP 1: MARKER ──────────────────────────────────────────────────────────
const markerInput = document.getElementById('marker-file-input');

// Drag & drop
const markerDZ = document.getElementById('marker-drop-zone');
markerDZ.addEventListener('dragover', e => { e.preventDefault(); markerDZ.classList.add('drag-over'); });
markerDZ.addEventListener('dragleave', () => markerDZ.classList.remove('drag-over'));
markerDZ.addEventListener('drop', e => {
    e.preventDefault(); markerDZ.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) handleMarkerFile(file);
});

markerInput.addEventListener('change', () => {
    if (markerInput.files[0]) handleMarkerFile(markerInput.files[0]);
});

async function handleMarkerFile(file) {
    state.markerImageUrl = URL.createObjectURL(file);
    document.getElementById('marker-img-preview').src = state.markerImageUrl;
    document.getElementById('marker-fname').textContent = file.name;
    document.getElementById('marker-drop-zone').classList.add('d-none');
    document.getElementById('marker-after-upload').classList.remove('d-none');
    document.getElementById('mstatus-uploading').classList.remove('d-none');

    const fd = new FormData();
    fd.append('image', file);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const res = await fetch('/api/markers', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: fd,
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'HTTP ' + res.status);
        }
        const data = await res.json();
        state.markerId = data.marker_id;

        document.getElementById('mstatus-uploading').classList.add('d-none');
        document.getElementById('mstatus-processing').classList.remove('d-none');
        startMarkerPolling();
    } catch (e) {
        console.error('Marker upload error:', e);
        document.getElementById('mstatus-uploading').classList.add('d-none');
        document.getElementById('mstatus-failed').classList.remove('d-none');
    }
}

function startMarkerPolling() {
    state.markerPollingTimer = setInterval(async () => {
        try {
            const res  = await fetch(`/api/marker/${state.markerId}`);
            const data = await res.json();
            if (data.status === 'ready') {
                clearInterval(state.markerPollingTimer);
                document.getElementById('mstatus-processing').classList.add('d-none');
                document.getElementById('mstatus-ready').classList.remove('d-none');
                document.getElementById('btn-next-1').disabled = false;
                state.markerStatus = 'ready';
            } else if (data.status === 'failed') {
                clearInterval(state.markerPollingTimer);
                document.getElementById('mstatus-processing').classList.add('d-none');
                document.getElementById('mstatus-failed').classList.remove('d-none');
            }
        } catch (_) {}
    }, 3000);
}

window.resetMarkerUpload = () => {
    clearInterval(state.markerPollingTimer);
    state.markerId = null; state.markerStatus = null; state.markerImageUrl = null;
    document.getElementById('marker-after-upload').classList.add('d-none');
    document.getElementById('marker-drop-zone').classList.remove('d-none');
    ['mstatus-uploading','mstatus-processing','mstatus-ready','mstatus-failed'].forEach(id => {
        document.getElementById(id).classList.add('d-none');
    });
    document.getElementById('btn-next-1').disabled = true;
    markerInput.value = '';
    document.getElementById('marker-fname').textContent = '';
    document.querySelectorAll('.marker-card').forEach(c => c.classList.remove('selected'));
};

async function loadMarkerLibrary() {
    const grid = document.getElementById('marker-grid');
    grid.innerHTML = '<div class="text-muted small"><div class="spinner-border spinner-border-sm me-2"></div>Memuat marker siap pakai...</div>';

    try {
        const res = await fetch('/api/markers');
        if (!res.ok) throw new Error('Gagal memuat marker');
        const markers = await res.json();
        if (!markers.length) {
            grid.innerHTML = '<div class="text-muted small">Belum ada marker siap pakai. Upload marker sendiri untuk melanjutkan.</div>';
            return;
        }

        grid.innerHTML = markers.map(marker => `
            <div class="marker-card" data-id="${marker.id}" data-image-url="${marker.image_url}">
                <img src="${marker.image_url}" alt="Marker ${marker.id}">
                <div class="marker-name">Marker #${marker.id}</div>
            </div>
        `).join('');

        grid.querySelectorAll('.marker-card').forEach(card => {
            card.addEventListener('click', () => {
                selectExistingMarker(card.dataset.id, card.dataset.imageUrl);
            });
        });
    } catch (e) {
        console.error('Marker library error:', e);
        grid.innerHTML = '<div class="text-danger small">Gagal memuat marker. Refresh halaman atau coba lagi nanti.</div>';
    }
}

window.selectExistingMarker = (markerId, imageUrl) => {
    state.markerId = Number(markerId);
    state.markerStatus = 'ready';
    state.markerImageUrl = imageUrl;

    document.getElementById('marker-img-preview').src = imageUrl;
    document.getElementById('marker-fname').textContent = `Marker #${markerId}`;
    document.getElementById('marker-drop-zone').classList.add('d-none');
    document.getElementById('marker-after-upload').classList.remove('d-none');
    document.getElementById('mstatus-uploading').classList.add('d-none');
    document.getElementById('mstatus-processing').classList.add('d-none');
    document.getElementById('mstatus-ready').classList.remove('d-none');
    document.getElementById('mstatus-failed').classList.add('d-none');
    document.getElementById('btn-next-1').disabled = false;

    document.querySelectorAll('.marker-card').forEach(c => c.classList.toggle('selected', c.dataset.id === String(markerId)));
};

loadMarkerLibrary();

// ─── STEP 2: MODE ────────────────────────────────────────────────────────────
window.switchMode = (mode) => {
    state.mode = mode;
    ['template','gltf','blend'].forEach(m => {
        document.getElementById(`mode-${m}`).classList.toggle('d-none', m !== mode);
        document.getElementById(`tab-${m}`).classList.toggle('active', m === mode);
    });
    checkStep2();
};

function isStep2Complete() {
    if (state.mode === 'template') return !!state.selectedTemplateId;
    if (state.mode === 'gltf')     return !!state.gltfFile;
    if (state.mode === 'blend')    return state.blendStatus === 'done';
    return false;
}

function checkStep2() {
    document.getElementById('btn-next-2').disabled = !isStep2Complete();
}

// Load templates
fetch('/api/templates')
    .then(r => r.json())
    .then(templates => {
        const grid = document.getElementById('template-grid');
        if (!templates.length) {
            grid.innerHTML = '<p class="text-muted small">Belum ada template tersedia.</p>';
            return;
        }
        grid.innerHTML = templates.map(t => `
            <div class="tpl-card" id="tpl-${t.id}" onclick="selectTemplate(${t.id}, '${t.model_url}', '${t.name}', ${JSON.stringify(t.placeholders ?? [])})">
                <div class="tpl-thumb">
                    ${t.thumbnail ? `<img src="${t.thumbnail}" style="max-height:100%;max-width:100%;object-fit:cover;border-radius:4px">` : '<i class="bi bi-box"></i>'}
                </div>
                <div class="tpl-name">${t.name}</div>
            </div>
        `).join('');
    })
    .catch(() => {
        document.getElementById('template-grid').innerHTML = '<p class="text-danger small">Gagal memuat template.</p>';
    });

window.selectTemplate = (id, url, name, placeholders) => {
    state.selectedTemplateId = id;
    state.selectedTemplateName = name;
    state.selectedTemplateUrl = url;

    document.querySelectorAll('.tpl-card').forEach(c => c.classList.remove('selected'));
    document.getElementById(`tpl-${id}`)?.classList.add('selected');

    // Config fields
    const area = document.getElementById('tpl-config-area');
    const fields = document.getElementById('tpl-config-fields');
    if (placeholders && placeholders.length) {
        area.classList.remove('d-none');
        fields.innerHTML = placeholders.map(ph => `
            <div class="mb-3">
                <label class="ar-label">${ph.label ?? ph.key}</label>
                <input type="text" class="ar-input" id="tpl-field-${ph.key}" placeholder="${ph.placeholder ?? ''}" value="${ph.default ?? ''}">
            </div>
        `).join('');
    } else {
        area.classList.add('d-none');
    }

    checkStep2();
};

// GLTF upload
const gltfInput = document.getElementById('gltf-file-input');
gltfInput.addEventListener('change', () => {
    const file = gltfInput.files[0];
    if (!file) return;
    state.gltfFile = file;
    state.gltfBlob = URL.createObjectURL(file);
    document.getElementById('gltf-fname').textContent = file.name;
    document.getElementById('gltf-drop-zone').classList.add('d-none');
    document.getElementById('gltf-chosen').classList.remove('d-none');
    checkStep2();
});

window.resetGltf = () => {
    state.gltfFile = null; state.gltfBlob = null;
    document.getElementById('gltf-drop-zone').classList.remove('d-none');
    document.getElementById('gltf-chosen').classList.add('d-none');
    gltfInput.value = '';
    checkStep2();
};

// BLEND upload — immediately upload & poll conversion job
const blendInput = document.getElementById('blend-file-input');
blendInput.addEventListener('change', async () => {
    const file = blendInput.files[0];
    if (!file) return;

    document.getElementById('blend-drop-zone').classList.add('d-none');
    document.getElementById('blend-after-upload').classList.remove('d-none');
    document.getElementById('blend-uploading').classList.remove('d-none');
    state.blendStatus = 'uploading';

    const fd = new FormData();
    fd.append('model', file);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const res = await fetch('/api/blend-upload', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: fd,
        });

        if (res.status === 413) {
            throw new Error('File terlalu besar. Naikan upload_max_filesize & post_max_size di php.ini dan client_max_body_size di Nginx.');
        }
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || `Server error HTTP ${res.status}`);
        }

        const data = await res.json();
        state.blendProjectId = data.project_id;

        document.getElementById('blend-uploading').classList.add('d-none');
        document.getElementById('blend-processing').classList.remove('d-none');
        state.blendStatus = 'processing';
        startBlendPolling();
    } catch (e) {
        console.error('Blend upload error:', e);
        document.getElementById('blend-uploading').classList.add('d-none');
        document.getElementById('blend-failed').classList.remove('d-none');
        document.getElementById('blend-error-msg').textContent = e.message || 'Upload gagal';
        state.blendStatus = 'failed';
    }
});

function startBlendPolling() {
    let pollCount = 0;
    const MAX_POLLS = 90; // 90 × 4 detik = 6 menit timeout maksimal

    state.blendPollingTimer = setInterval(async () => {
        pollCount++;

        // Timeout guard — hentikan polling setelah 6 menit
        if (pollCount > MAX_POLLS) {
            clearInterval(state.blendPollingTimer);
            state.blendStatus = 'failed';
            document.getElementById('blend-processing').classList.add('d-none');
            document.getElementById('blend-failed').classList.remove('d-none');
            document.getElementById('blend-error-msg').textContent = 'Timeout: konversi melebihi 6 menit.';
            return;
        }

        try {
            const res  = await fetch(`/api/blend-status/${state.blendProjectId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return; // skip — jangan stop polling karena network glitch

            const data = await res.json();

            // KRITIS: status 'ready' hanya valid jika model_url juga sudah ada.
            // Ini mencegah false-positive saat DB sudah terupdate tapi
            // proses Blender belum selesai menulis file ke disk.
            if (data.status === 'ready' && data.model_url) {
                clearInterval(state.blendPollingTimer);
                state.blendGlbUrl = data.model_url;
                state.blendStatus = 'done';
                document.getElementById('blend-processing').classList.add('d-none');
                document.getElementById('blend-done').classList.remove('d-none');
                checkStep2();

            } else if (data.status === 'failed') {
                clearInterval(state.blendPollingTimer);
                state.blendStatus = 'failed';
                document.getElementById('blend-processing').classList.add('d-none');
                document.getElementById('blend-failed').classList.remove('d-none');
                document.getElementById('blend-error-msg').textContent = 'Konversi Blender gagal di server.';

            }
            // status === 'processing' → lanjutkan polling
        } catch(_) {
            // Network error — lanjutkan polling, jangan stop
        }
    }, 4000);
}

window.resetBlend = () => {
    clearInterval(state.blendPollingTimer);
    state.blendProjectId = null; state.blendGlbUrl = null; state.blendStatus = null;
    document.getElementById('blend-drop-zone').classList.remove('d-none');
    document.getElementById('blend-after-upload').classList.add('d-none');
    ['blend-uploading','blend-processing','blend-done','blend-failed'].forEach(id => {
        document.getElementById(id).classList.add('d-none');
    });
    blendInput.value = '';
    checkStep2();
};

// ─── STEP 3: PREVIEW ─────────────────────────────────────────────────────────
function enterStep3() {
    if (!renderer) initThree();

    document.getElementById('preview-marker-thumb').src = state.markerImageUrl;

    // ── Load marker sebagai plane 3D di scene ─────────────────────────────────
    loadMarkerPlane(state.markerImageUrl);

    // ── Load model 3D ─────────────────────────────────────────────────────────
    let modelUrl = null;
    if (state.mode === 'template' && state.selectedTemplateUrl) {
        modelUrl = state.selectedTemplateUrl;
    } else if (state.mode === 'gltf' && state.gltfBlob) {
        modelUrl = state.gltfBlob;
    } else if (state.mode === 'blend' && state.blendGlbUrl) {
        modelUrl = state.blendGlbUrl;
    }

    if (modelUrl) {
        loadModelIntoPreview(modelUrl);
    } else {
        document.getElementById('canvas-loading').innerHTML = '<p class="text-muted small">Tidak ada model untuk di-preview.</p>';
    }
}

// ── Marker plane 3D ──────────────────────────────────────────────────────────
// ── Marker indicator di lantai ────────────────────────────────────────────
function loadMarkerPlane(/*imageUrl*/) {
    if (markerPlane) { scene.remove(markerPlane); markerPlane = null; }

    const createLabelSprite = (text, color) => {
        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 128;
        const ctx = canvas.getContext('2d');
        ctx.font = '700 48px Arial';
        ctx.fillStyle = color;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, canvas.width / 2, canvas.height / 2);
        const texture = new THREE.CanvasTexture(canvas);
        texture.encoding = THREE.sRGBColorSpace;
        const material = new THREE.SpriteMaterial({ map: texture, transparent: true, depthTest: false, depthWrite: false });
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(0.3, 0.15, 1);
        return sprite;
    };

    const markerGroup = new THREE.Group();
    markerGroup.position.set(0, 0.001, 0);

    const armLen = 0.4;
    const armW = 0.05;
    const plusMat = new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.95, depthTest: false });

    const hMesh = new THREE.Mesh(new THREE.PlaneGeometry(armLen * 2, armW), plusMat);
    hMesh.rotation.x = -Math.PI / 2;
    const vMesh = new THREE.Mesh(new THREE.PlaneGeometry(armW, armLen * 2), plusMat);
    vMesh.rotation.x = -Math.PI / 2;

    const dot = new THREE.Mesh(
        new THREE.CircleGeometry(armW * 0.8, 24),
        new THREE.MeshBasicMaterial({ color: 0x6366f1, transparent: true, opacity: 1, depthTest: false })
    );
    dot.rotation.x = -Math.PI / 2;
    dot.position.y = 0.001;

    const axisMaterialX = new THREE.LineBasicMaterial({ color: 0xef4444, transparent: true, opacity: 0.9, depthTest: false });
    const axisMaterialY = new THREE.LineBasicMaterial({ color: 0x22c55e, transparent: true, opacity: 0.9, depthTest: false });
    const axisMaterialZ = new THREE.LineBasicMaterial({ color: 0x3b82f6, transparent: true, opacity: 0.9, depthTest: false });

    const xLine = new THREE.Line(
        new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(-armLen, 0.001, 0),
            new THREE.Vector3(armLen, 0.001, 0)
        ]),
        axisMaterialX
    );

    const zLine = new THREE.Line(
        new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(0, 0.001, -armLen),
            new THREE.Vector3(0, 0.001, armLen)
        ]),
        axisMaterialZ
    );

    const yLine = new THREE.Line(
        new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(0, 0.001, 0),
            new THREE.Vector3(0, 0.4, 0)
        ]),
        axisMaterialY
    );

    const xLabel = createLabelSprite('X', '#ef4444');
    xLabel.position.set(armLen + 0.15, 0.01, 0);

    const zLabel = createLabelSprite('Z', '#3b82f6');
    zLabel.position.set(0, 0.01, armLen + 0.15);

    const yLabel = createLabelSprite('Y', '#22c55e');
    yLabel.position.set(0, 0.45, 0);

    markerGroup.add(hMesh, vMesh, dot, xLine, zLine, yLine, xLabel, zLabel, yLabel);
    markerGroup.name = 'markerGroup';
    markerPlane = markerGroup;
    scene.add(markerGroup);

    camera.position.set(0, 2.5, 3.5);
    orbitControls.target.set(0, 0.3, 0);
    orbitControls.update();
}

// ─── STEP 4: GENERATE REVIEW ─────────────────────────────────────────────────
function populateGenerateReview() {
    document.getElementById('gen-marker-img').src = state.markerImageUrl;

    const typeLabels = { template: 'Template', gltf: 'GLB/GLTF', blend: 'Blend (dikonversi)' };
    document.getElementById('gen-type').textContent   = typeLabels[state.mode] || state.mode;

    let modelName = '—';
    if (state.mode === 'template') modelName = state.selectedTemplateName;
    else if (state.mode === 'gltf')    modelName = state.gltfFile?.name;
    else if (state.mode === 'blend')   modelName = `Project #${state.blendProjectId}`;
    document.getElementById('gen-model').textContent = modelName;

    const s = parseFloat(document.getElementById('scale-slider').value);
    state.scale = s;
    state.position = [
        parseFloat(document.getElementById('pos-x').value)||0,
        parseFloat(document.getElementById('pos-y').value)||0,
        parseFloat(document.getElementById('pos-z').value)||0,
    ];
    state.rotation = [
        parseFloat(document.getElementById('rot-x').value)||0,
        parseFloat(document.getElementById('rot-y').value)||0,
        parseFloat(document.getElementById('rot-z').value)||0,
    ];

    document.getElementById('gen-scale').textContent    = s.toFixed(2);
    document.getElementById('gen-position').textContent = `X: ${state.position[0]}, Y: ${state.position[1]}, Z: ${state.position[2]}`;
    document.getElementById('gen-rotation').textContent = `X: ${state.rotation[0]}°, Y: ${state.rotation[1]}°, Z: ${state.rotation[2]}°`;
}

// ── Orbit controls ───────────────────────────────────────────────────────────
window.toggleOrbit = () => {
    orbitState.active = !orbitState.active;
    const btn  = document.getElementById('btn-orbit');
    const icon = document.getElementById('orbit-icon');

    if (orbitState.active) {
        icon.className  = 'bi bi-pause-circle';
        btn.innerHTML   = '<i class="bi bi-pause-circle" id="orbit-icon"></i> Pause Orbit';
        btn.style.borderColor = 'var(--primary)';
        btn.style.color       = 'var(--primary)';

        // Reset pivot ke radius awal
        orbitState.angle = 0;
        if (pivotGroup) {
            pivotGroup.position.set(0, 0, orbitState.radius);
            pivotGroup.rotation.y = 0;
        }
    } else {
        icon.className  = 'bi bi-play-circle';
        btn.innerHTML   = '<i class="bi bi-play-circle" id="orbit-icon"></i> Mulai Orbit';
        btn.style.borderColor = '';
        btn.style.color       = '';

        // Kembalikan ke tengah marker
        if (pivotGroup) {
            pivotGroup.position.set(0, 0, 0);
            pivotGroup.rotation.set(0, 0, 0);
        }
    }
};

window.toggleOrbitDir = () => {
    orbitState.dir *= -1;
    document.getElementById('orbit-dir-icon').style.transform =
        orbitState.dir === 1 ? '' : 'scaleX(-1)';
};

document.getElementById('orbit-speed')?.addEventListener('input', function () {
    orbitState.speed = parseFloat(this.value);
    document.getElementById('orbit-speed-val').textContent = this.value + '×';
});

document.getElementById('orbit-radius')?.addEventListener('input', function () {
    orbitState.radius = parseFloat(this.value);
    document.getElementById('orbit-radius-val').textContent = this.value;
});

// ── Switch animasi clip ───────────────────────────────────────────────────────
window.switchAnimClip = (index) => {
    if (!mixer || !allClips[index]) return;

    // Fade out action sekarang
    if (activeAction) {
        activeAction.fadeOut(0.3);
    }

    // Fade in action baru
    activeAction = mixer.clipAction(allClips[index]);
    activeAction.reset().fadeIn(0.3).play();
};

window.submitGenerate = () => {

    const baseScale = previewModel?.userData._baseScale || 1;
    const bottomY   = previewModel?.userData._bottomY   || 0;

    // Scale: kirim nilai absolut (slider × baseScale)
    const scaleAR = state.scale * baseScale;

    // Posisi Y: kurangi bottomY karena AR tidak punya offset otomatis
    const posYAR = state.position[1] - bottomY;

    document.getElementById('form-scale').value    = scaleAR;
    document.getElementById('form-position').value = JSON.stringify([
        state.position[0],
        posYAR,
        state.position[2],
    ]);
    // Fill form
    document.getElementById('form-marker-id').value  = state.markerId;
    document.getElementById('form-type').value        = state.mode;
    document.getElementById('form-scale').value        = scaleAR;
    document.getElementById('form-position').value     = JSON.stringify([state.position[0],posYAR,state.position[2],]);
    document.getElementById('form-rotation').value     = JSON.stringify(state.rotation);
    document.getElementById('form-orbit-active').value = orbitState.active ? '1' : '0';
    document.getElementById('form-orbit-speed').value  = orbitState.speed;
    document.getElementById('form-orbit-radius').value = orbitState.radius;
    document.getElementById('form-orbit-dir').value    = orbitState.dir;
    // Clip yang dipilih saat ini
    const clipSel = document.getElementById('anim-clip-select');
    // Jika hanya ada satu clip di GLB preview, kirim nama clip tersebut
    let animClipValue = '*';
    if (Array.isArray(allClips) && allClips.length === 1) {
        animClipValue = allClips[0].name || '*';
    } else if (clipSel) {
        const val = clipSel.value || '*';
        if (val === '*') {
            animClipValue = '*';
        } else {
            // clipSel value bisa berupa index (dari preview) atau nama (fallback)
            const idx = parseInt(val);
            if (!isNaN(idx) && Array.isArray(allClips) && allClips[idx] && allClips[idx].name) {
                animClipValue = allClips[idx].name;
            } else {
                animClipValue = val;
            }
        }
    }
    document.getElementById('form-anim-clip').value = animClipValue;

    if (state.mode === 'template') {
        document.getElementById('form-template-id').value = state.selectedTemplateId;
        // Collect config fields
        const configContainer = document.getElementById('tpl-config-fields');
        const configHidden    = document.getElementById('form-config-fields');
        configHidden.innerHTML = '';
        configContainer.querySelectorAll('input[id^="tpl-field-"]').forEach(inp => {
            const key = inp.id.replace('tpl-field-', '');
            const h   = document.createElement('input');
            h.type = 'hidden'; h.name = `config[${key}]`; h.value = inp.value;
            configHidden.appendChild(h);
        });
    } else if (state.mode === 'gltf' && state.gltfFile) {
        const dt = new DataTransfer();
        dt.items.add(state.gltfFile);
        document.getElementById('form-model-file').files = dt.files;
    } else if (state.mode === 'blend') {
        // Blend was already processed server-side; pass project id
        document.getElementById('form-blend-project-id').value = state.blendProjectId;
    }

    document.getElementById('generate-form').submit();
};

</script>
@endpush