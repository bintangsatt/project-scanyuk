<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>AR Viewer</title>

    <script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mind-ar@1.2.5/dist/mindar-image-aframe.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/donmccurdy/aframe-extras@v7.0.0/dist/aframe-extras.min.js"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #000; }

        /* Loading */
        #loading-overlay {
            position: fixed; inset: 0; z-index: 1000;
            background: linear-gradient(135deg, #0f0f14, #1a1a2e);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: #fff; gap: 16px; transition: opacity .5s;
        }
        #loading-overlay.fade-out { opacity: 0; pointer-events: none; }
        .loading-logo { font-size: 3rem; }
        .loading-spinner {
            width: 40px; height: 40px;
            border: 3px solid rgba(255,255,255,.1); border-top-color: #6366f1;
            border-radius: 50%; animation: spin .9s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        #loading-status { font-size: .87rem; color: #94a3b8; text-align: center; max-width: 260px; line-height: 1.5; }
        .loading-bar-wrap { width: 180px; height: 3px; background: rgba(255,255,255,.1); border-radius: 99px; overflow: hidden; }
        .loading-bar { height: 100%; width: 30%; background: linear-gradient(90deg,#6366f1,#818cf8); border-radius: 99px; animation: lbar 1.6s ease-in-out infinite; }
        @keyframes lbar { 0%{transform:translateX(-100%)} 100%{transform:translateX(400%)} }

        /* Error */
        #error-overlay {
            position: fixed; inset: 0; z-index: 1001;
            background: rgba(10,10,16,.96); display: none;
            flex-direction: column; align-items: center; justify-content: center;
            color: #fff; gap: 14px; padding: 32px;
        }
        #error-overlay.show { display: flex; }
        .error-icon  { font-size: 2.8rem; }
        .error-title { font-size: 1.05rem; font-weight: 700; color: #f87171; }
        .error-msg   { font-size: .84rem; color: #94a3b8; text-align: center; line-height: 1.6; max-width: 290px; }
        .error-btn   { background: #6366f1; color: #fff; border: none; padding: 10px 28px; border-radius: 8px; font-size: .88rem; font-weight: 600; cursor: pointer; margin-top: 4px; }

        /* Scanning */
        #scanning-overlay {
            position: fixed; inset: 0; z-index: 90;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            pointer-events: none; opacity: 0; transition: opacity .4s;
        }
        #scanning-overlay.visible { opacity: 1; }
        .scan-frame { width: min(240px,62vw); aspect-ratio: 1; position: relative; }
        .scan-frame::before,.scan-frame::after,.scan-inner::before,.scan-inner::after {
            content:''; position:absolute; width:26px; height:26px; border-color:#6366f1; border-style:solid;
        }
        .scan-frame::before  { top:0; left:0;   border-width:3px 0 0 3px; border-radius:4px 0 0 0; }
        .scan-frame::after   { top:0; right:0;  border-width:3px 3px 0 0; border-radius:0 4px 0 0; }
        .scan-inner::before  { bottom:0; left:0;  border-width:0 0 3px 3px; border-radius:0 0 0 4px; }
        .scan-inner::after   { bottom:0; right:0; border-width:0 3px 3px 0; border-radius:0 0 4px 0; }
        .scan-line {
            position:absolute; left:5%; right:5%; height:2px;
            background:linear-gradient(90deg,transparent,#6366f1,#818cf8,transparent);
            box-shadow:0 0 8px #6366f1; animation:scanmove 2s ease-in-out infinite;
        }
        @keyframes scanmove { 0%{top:5%} 50%{top:90%} 100%{top:5%} }
        .scan-label { margin-top:22px; font-size:.84rem; font-weight:600; color:rgba(255,255,255,.85); text-transform:uppercase; letter-spacing:.05em; text-shadow:0 1px 8px rgba(0,0,0,.8); animation:ptxt 2s ease-in-out infinite; }
        @keyframes ptxt { 0%,100%{opacity:.65} 50%{opacity:1} }

        /* Found badge */
        #found-badge {
            position:fixed; top:16px; left:50%; transform:translateX(-50%);
            z-index:91; background:rgba(16,185,129,.88); color:#fff;
            padding:5px 16px; border-radius:99px; font-size:.8rem; font-weight:600;
            display:flex; align-items:center; gap:6px;
            opacity:0; transition:opacity .3s; backdrop-filter:blur(6px); white-space:nowrap;
        }
        #found-badge.visible { opacity:1; }
        #found-badge .dot { width:6px; height:6px; border-radius:50%; background:#fff; animation:blink 1.2s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

        /* Orbit badge */
        #orbit-badge {
            position:fixed; top:52px; left:50%; transform:translateX(-50%);
            z-index:91; background:rgba(99,102,241,.8); color:#fff;
            padding:4px 14px; border-radius:99px; font-size:.75rem; font-weight:600;
            opacity:0; transition:opacity .3s; backdrop-filter:blur(6px); white-space:nowrap;
            display:flex; align-items:center; gap:5px;
        }
        #orbit-badge.visible { opacity:1; }

        /* World-lock badge */
        #worldlock-badge {
            position:fixed; top:16px; left:50%; transform:translateX(-50%);
            z-index:92; background:rgba(16,185,129,.92); color:#fff;
            padding:6px 18px; border-radius:99px; font-size:.82rem; font-weight:700;
            display:none; align-items:center; gap:8px;
            backdrop-filter:blur(8px); white-space:nowrap;
            box-shadow: 0 2px 12px rgba(16,185,129,.35);
        }
        #worldlock-badge.visible { display:flex; }
        #worldlock-badge .lock-icon { font-size:1rem; }

        /* World-lock hint (muncul sesaat setelah locked) */
        #worldlock-hint {
            position:fixed; bottom:80px; left:50%; transform:translateX(-50%);
            z-index:92; background:rgba(0,0,0,.72); color:#fff;
            padding:8px 20px; border-radius:12px; font-size:.8rem;
            text-align:center; line-height:1.5; max-width:260px;
            opacity:0; transition:opacity .5s; pointer-events:none;
            backdrop-filter:blur(6px);
        }
        #worldlock-hint.visible { opacity:1; }

        /* Clip selector */
        #clip-wrap {
            position:fixed; top:16px; right:14px; z-index:95;
            display:none; flex-direction:column; gap:4px; align-items:flex-end;
        }
        #clip-wrap.show { display:flex; }
        #clip-label { font-size:.68rem; color:rgba(255,255,255,.45); text-transform:uppercase; letter-spacing:.06em; }
        #clip-select {
            background:rgba(0,0,0,.65); color:#fff;
            border:1px solid rgba(255,255,255,.2); border-radius:8px;
            padding:5px 10px; font-size:.8rem; backdrop-filter:blur(8px); max-width:150px;
        }

        /* Bottom UI */
        #ui-panel {
            position:fixed; bottom:0; left:0; right:0; z-index:95;
            padding:14px 18px 26px;
            background:linear-gradient(to top,rgba(0,0,0,.65) 0%,transparent 100%);
            display:flex; align-items:center; justify-content:space-between; gap:10px;
        }
        .btn-ui {
            background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
            color:#fff; padding:8px 18px; border-radius:99px; font-size:.83rem;
            font-weight:600; cursor:pointer; backdrop-filter:blur(8px);
            display:flex; align-items:center; gap:5px; transition:background .2s;
        }
        .btn-ui:active { background:rgba(255,255,255,.25); }
        .btn-ui.primary { background:rgba(99,102,241,.8); border-color:rgba(99,102,241,.5); }
    </style>

    <script>
    AFRAME.registerComponent('ar-adaptive-pov', {
        schema: {
            enabled: { type: 'boolean', default: true },
        },

        init() {
            // Tidak ada setup khusus — tracking dilakukan MindAR secara native
        },

        tick() {
            // Sengaja dikosongkan: object mengikuti marker secara alami
            // tanpa rotasi tambahan. MindAR sudah update worldMatrix marker
            // setiap frame berdasarkan posisi kamera realtime.
        },
    });

    AFRAME.registerComponent('ar-orbit-pivot', {
        schema: {
            active: { type: 'boolean', default: false },
            speed:  { type: 'number',  default: 0.5  },
            dir:    { type: 'number',  default: 1    },
        },

        init() {
            this.angle = 0;
            this._camWorldPos    = new THREE.Vector3();
            this._markerWorldPos = new THREE.Vector3();
            this._dir            = new THREE.Vector3();
        },

        update(oldData) {
            if (oldData.active === true && this.data.active === false) {
                this.angle = 0;
                this.el.object3D.rotation.set(0, 0, 0);
                // Re-enable POV saat orbit berhenti
                const pov = this.el.components['ar-adaptive-pov'];
                if (pov) pov.data.enabled = true;
                // Nonaktifkan occluder saat orbit OFF
                this._setOccluder(false);
            }
            if (oldData.active === false && this.data.active === true) {
                // Aktifkan occluder saat orbit ON
                this._setOccluder(true);
            }
        },

        _setOccluder(active) {
            const occEl = document.getElementById('marker-occluder');
            if (occEl) occEl.setAttribute('ar-marker-occluder', `active: ${active}`);
        },

        tick(time, deltaMs) {
            if (!this.data.active) return;

            // Nonaktifkan POV saat orbit aktif
            const pov = this.el.components['ar-adaptive-pov'];
            if (pov && pov.data.enabled) pov.data.enabled = false;

            const delta = deltaMs / 1000;
            this.angle += this.data.speed * this.data.dir * delta;

            // Orbit murni di sumbu Y (lokal pivot = sumbu marker)
            // Occluder (ar-marker-occluder) yang bertanggung jawab
            // menyembunyikan object saat lewat di belakang marker
            // sehingga kita tidak perlu mengubah sumbu orbit di sini.
            this.el.object3D.rotation.set(0, this.angle, 0);
        },

        // Dipanggil dari luar (misal tombol UI) untuk toggle orbit
        setOrbit(active) {
            this.el.setAttribute('ar-orbit-pivot', 'active', active);
        },
    });

    AFRAME.registerComponent('ar-marker-occluder', {
        schema: {
            padding: { type: 'number',  default: 0.05  }, // margin sedikit agar pas
            active:  { type: 'boolean', default: false }, // hanya aktif saat orbit ON
        },

        init() {
            this._mesh    = null;
            this._built   = false;
            this._visible = false;

            // Bangun saat marker pertama kali ditemukan
            // (saat targetFound, controller sudah punya corner data)
            const targetEl = this.el.closest('[mindar-image-target]')
                          || this.el.parentEl;

            targetEl.addEventListener('targetFound', () => {
                this._visible = true;
                if (!this._built) this._buildFromCorners();
                this._applyVisibility();
            });
            targetEl.addEventListener('targetLost', () => {
                this._visible = false;
                this._applyVisibility();
            });
        },

        update() {
            // Dipanggil saat schema berubah (termasuk active)
            this._applyVisibility();
        },

        _applyVisibility() {
            if (this._mesh) {
                // Occluder terlihat (menulis depth) HANYA jika visible DAN active
                this._mesh.visible = this._visible && this.data.active;
            }
        },

        _buildFromCorners() {
            let w = 1, h = 1;

            try {
                const sceneComp = this.el.sceneEl.components['mindar-image'];
                const ctrl      = sceneComp?.controller;

                // Coba baca target entity index
                const targetEl  = this.el.closest('[mindar-image-target]')
                               || this.el.parentEl;
                const targetIdx = parseInt(
                    targetEl?.getAttribute('mindar-image-target')
                        ?.match(/targetIndex:\s*(\d+)/)?.[1] ?? '0'
                );

                const imgTarget = ctrl?.imageTargets?.[targetIdx];
                if (imgTarget?.dimensions) {
                    // dimensions = [width_px, height_px] dari gambar asli
                    w = 1;
                    h = imgTarget.dimensions[1] / imgTarget.dimensions[0];
                    console.log('[occluder] Aspect ratio dari MindAR:', w.toFixed(3), '×', h.toFixed(3));
                }
            } catch(e) {
                console.warn('[occluder] Gagal baca MindAR dimensions, fallback 1×1:', e);
            }

            const pad = this.data.padding;
            const hw  = w / 2 + pad; // half-width + padding
            const hh  = h / 2 + pad; // half-height + padding

            // ── Bangun ShapeGeometry dari 4 corners ──────────────────────────
            // ShapeGeometry mendukung polygon arbitrer — bisa ganti corners
            // ini dengan bentuk apapun (oval, L-shape, dll) di masa depan.
            const shape = new THREE.Shape([
                new THREE.Vector2(-hw,  hh), // top-left
                new THREE.Vector2( hw,  hh), // top-right
                new THREE.Vector2( hw, -hh), // bottom-right
                new THREE.Vector2(-hw, -hh), // bottom-left
            ]);

            const geo = new THREE.ShapeGeometry(shape);
            const mat = new THREE.MeshBasicMaterial({
                colorWrite: false,      // tidak terlihat
                depthWrite: true,       // tulis depth buffer
                side:       THREE.FrontSide, // hanya sisi depan (hadap kamera)
            });

            const mesh = new THREE.Mesh(geo, mat);

            // Z=0, tepat di permukaan marker, menghadap ke depan (kamera)
            // ShapeGeometry default di bidang XY — sudah benar untuk marker space
            mesh.position.set(0, 0, 0);
            mesh.renderOrder = 0; // render sebelum model (renderOrder 1)
            mesh.visible = false; // mulai tersembunyi, akan di-toggle oleh ar-orbit-pivot

            this._removeMesh();
            this.el.object3D.add(mesh);
            this._mesh  = mesh;
            this._built = true;

            // Sinkronkan visibility sesuai state saat ini
            this._applyVisibility();

            console.log(
                '[occluder] Polygon Z=0 siap ✓',
                `${(hw*2).toFixed(3)} × ${(hh*2).toFixed(3)}`,
                'corners: (±', hw.toFixed(3), ', ±', hh.toFixed(3), ')'
            );
        },

        _removeMesh() {
            if (this._mesh) {
                this._mesh.geometry.dispose();
                this._mesh.material.dispose();
                this.el.object3D.remove(this._mesh);
                this._mesh = null;
            }
        },

        remove() { this._removeMesh(); },
    });

   AFRAME.registerComponent('ar-depth-fix', {
        init() {
            const apply = () => {
                this.el.object3D.traverse(node => {
                    if (node.isMesh) {
                        node.frustumCulled = false;
                        // renderOrder 1 → render SETELAH occluder (renderOrder 0)
                        node.renderOrder = 1;
                        if (node.material) {
                            const mats = Array.isArray(node.material)
                                ? node.material : [node.material];
                            mats.forEach(m => {
                                m.depthTest   = true;   // wajib agar occluder bisa block
                                m.depthWrite  = true;
                                // Pastikan tidak ada transparansi paksa yang bypass depth
                                if (m.transparent === undefined) m.transparent = false;
                                m.needsUpdate = true;
                            });
                        }
                    }
                });
            };
            this.el.addEventListener('model-loaded', apply);
            if (this.el.getObject3D('mesh')) apply();
        },
    });

    AFRAME.registerComponent('ar-world-lock', {
        schema: {
            enabled:  { type: 'boolean', default: true  },
            autolock: { type: 'boolean', default: false }, 
        },

        init() {
            this._locked     = false;
            this._firstFound = false;
            this._pivotEl    = document.getElementById('orbit-pivot');
            this._sceneEl    = this.el.sceneEl;

            this._onFound = this._onFound.bind(this);
            this.el.addEventListener('targetFound', this._onFound);
        },

        _onFound() {
            if (!this.data.enabled || this._locked) return;
            this._firstFound = true;
            if (this.data.autolock) {
                // 3 frame delay — pastikan MindAR sudah update worldMatrix pivot
                let ticks = 0;
                const waitFn = () => {
                    ticks++;
                    if (ticks >= 3) {
                        this._sceneEl.removeEventListener('tick', waitFn);
                        this._doLock();
                    }
                };
                this._sceneEl.addEventListener('tick', waitFn);
            }
        },

        _doLock() {
            if (this._locked || !this._pivotEl) return;

            const pivotEl    = this._pivotEl;
            const pivotObj3D = pivotEl.object3D;

            // ── 1. Capture world transform SEBELUM reparent ─────────────────
            const worldPos   = new THREE.Vector3();
            const worldQuat  = new THREE.Quaternion();
            const worldScale = new THREE.Vector3();
            pivotObj3D.getWorldPosition(worldPos);
            pivotObj3D.getWorldQuaternion(worldQuat);
            pivotObj3D.getWorldScale(worldScale);

            // ── 2. Reparent di level A-FRAME (bukan Three.js langsung) ──────
            // Ini yang membedakan v3: appendChild di DOM A-Frame
            // → semua komponen (animation-mixer, ar-orbit-pivot, dll) tetap aktif
            this._sceneEl.appendChild(pivotEl);

            // Terapkan world transform sebagai local transform baru
            // (karena parent baru adalah scene root = identitas)
            pivotEl.object3D.position.copy(worldPos);
            pivotEl.object3D.quaternion.copy(worldQuat);
            pivotEl.object3D.scale.copy(worldScale);

            // ── 3. Nonaktifkan ar-adaptive-pov ──────────────────────────────
            const povComp = pivotEl.components['ar-adaptive-pov'];
            if (povComp) povComp.data.enabled = false;

            // ── 4. Aktifkan ar-gyro-camera (bukan look-controls) ────────────
            // ar-gyro-camera membaca DeviceOrientationEvent → rotasi kamera nyata
            // look-controls dinonaktifkan — dia menggeser scene, bukan merotasi
            const camEl = this._sceneEl.querySelector('a-camera');
            if (camEl) {
                camEl.setAttribute('look-controls', 'enabled: false');
                camEl.setAttribute('ar-gyro-camera', '');
            }

            // ── 5. Hentikan MindAR image processing (hemat CPU/baterai) ─────
            try {
                const mc = this._sceneEl.components['mindar-image'];
                mc?.controller?.stopProcessVideo?.();
            } catch(e) {}

            this._locked = true;
            this._sceneEl.dispatchEvent(new CustomEvent('ar-world-locked'));
            console.log('[AR] World locked v3 ✓', worldPos);
        },

        remove() {
            this.el.removeEventListener('targetFound', this._onFound);
        },
    });

    AFRAME.registerComponent('ar-gyro-camera', {
        init() {
            this._euler    = new THREE.Euler();
            this._qBase    = null;   // orientasi saat komponen pertama aktif (baseline)
            this._qCurrent = new THREE.Quaternion();
            this._onOrient = this._onOrient.bind(this);

            // iOS 13+ butuh requestPermission
            if (typeof DeviceOrientationEvent?.requestPermission === 'function') {
                DeviceOrientationEvent.requestPermission()
                    .then(state => {
                        if (state === 'granted') {
                            window.addEventListener('deviceorientation', this._onOrient, true);
                        } else {
                            console.warn('[AR] Gyro permission denied');
                        }
                    })
                    .catch(console.error);
            } else {
                window.addEventListener('deviceorientation', this._onOrient, true);
            }
        },

        _onOrient(e) {
            if (e.alpha === null) return;

            const degToRad = THREE.MathUtils.degToRad;

            // Konversi orientasi device → quaternion
            // Urutan: ZXY sesuai standar W3C DeviceOrientation
            this._euler.set(
                degToRad(e.beta),
                degToRad(e.alpha),
                degToRad(-e.gamma),
                'ZXY'
            );
            this._qCurrent.setFromEuler(this._euler);

            // Simpan orientasi pertama sebagai baseline (kalibrasi)
            if (!this._qBase) {
                this._qBase = this._qCurrent.clone();
            }

            // Rotasi relatif dari baseline
            const qRelative = this._qBase.clone().invert().multiply(this._qCurrent);

            // Terapkan ke kamera
            // Offset -90° di X karena layar HP tegak = kamera hadap ke depan
            const qOffset = new THREE.Quaternion().setFromEuler(
                new THREE.Euler(degToRad(-90), 0, 0)
            );
            this.el.object3D.quaternion.copy(qOffset.multiply(qRelative));
        },

        remove() {
            window.removeEventListener('deviceorientation', this._onOrient, true);
        },
    });

    // ── Helper global ────────────────────────────────────────────────────────────
    window.arWorldLock  = () => {
        const comp = document.querySelector('[ar-world-lock]')?.components['ar-world-lock'];
        if (comp && comp._firstFound && !comp._locked) comp._doLock();
    };
    window.arWorldReset = () => location.reload();
    </script>
</head>
<body>

    <div id="loading-overlay">
        <div class="loading-logo">ScanGo</div>
        <div class="loading-spinner"></div>
        <div id="loading-status">Memuat engine AR...</div>
        <div class="loading-bar-wrap"><div class="loading-bar"></div></div>
    </div>

    <div id="error-overlay">
        <div class="error-icon">⚠️</div>
        <div class="error-title" id="error-title">Terjadi Kesalahan</div>
        <div class="error-msg"  id="error-msg">Terjadi kesalahan saat memuat AR.</div>
        <button class="error-btn" onclick="location.reload()">Coba Lagi</button>
    </div>

    <a-scene
        id="ar-scene"
        mindar-image="imageTargetSrc: {{ $mindUrl }}; uiLoading: no; uiError: no; uiScanning: no;"
        color-space="sRGB"
        renderer="colorManagement: true; sortObjects: true; antialias: true; logarithmicDepthBuffer: true"
        vr-mode-ui="enabled: false"
        device-orientation-permission-ui="enabled: false">

        <a-assets timeout="600000">
            @if($modelUrl)
            <a-asset-item id="ar-model" src="{{ $modelUrl }}"></a-asset-item>
            @endif
        </a-assets>

        <a-camera position="0 0 0" look-controls="enabled: false; magicWindowTrackingEnabled: false" near="0.001" far="1000"></a-camera>

        {{-- ar-world-lock dipasang di sini: mendengar targetFound dari entity ini --}}
        <a-entity mindar-image-target="targetIndex: 0"
                  ar-world-lock="enabled: true; autolock: false">

            {{--
                ── OCCLUDER PLANE Z=0 ───────────────────────────────────────────
                Plane flat tak terlihat TEPAT di permukaan marker (Z=0).
                Cara kerja:
                  - Object 3D orbit di Z positif (depan marker) → terlihat
                  - Object 3D orbit ke Z negatif (belakang marker) → depth
                    buffer plane menghalanginya → menghilang natural
                Ukuran otomatis dibaca dari imageTarget MindAR (rasio marker).
                padding: tambah margin jika perlu menutup lebih lebar.
            --}}
            <a-entity
                id="marker-occluder"
                ar-marker-occluder="padding: 0; active: {{ $orbitActive ? 'true' : 'false' }}"
                position="0 0 0">
            </a-entity>

            @if(!$isTemplate && $modelUrl)
            {{--
                STRUKTUR: Pivot → Model

                [pivot-entity]  ← ar-adaptive-pov + ar-orbit-pivot
                  position="0 Y 0"
                  ar-adaptive-pov : otomatis rotasi menghadap kamera (pitch+yaw)
                  ar-orbit-pivot  : jika orbit aktif, memutar pivot Y tiap tick
                                    (dan menonaktifkan POV adaptif selama orbit)

                  [model-entity]  ← child pivot
                    ar-depth-fix  : frustumCulled=false, depthTest=true,
                                    renderOrder=1 (render setelah occluder)

                INTERAKSI POV ↔ ORBIT:
                - Orbit OFF → ar-adaptive-pov aktif → object hadap kamera otomatis
                - Orbit ON  → ar-adaptive-pov dinonaktifkan, ar-orbit-pivot ambil alih
                - Orbit OFF lagi → ar-adaptive-pov kembali aktif
            --}}
            <a-entity
                id="orbit-pivot"
                position="0 {{ floatval($position[1] ?? 0) }} 0"
                rotation="0 0 0"
                ar-adaptive-pov="enabled: {{ $orbitActive ? 'false' : 'true' }}"
                ar-orbit-pivot="active: {{ $orbitActive ? 'true' : 'false' }}; speed: {{ $orbitSpeed }}; dir: {{ $orbitDir }}">

                <a-entity
                    id="model-container"
                    gltf-model="#ar-model"
                    @if($orbitActive)
                    position="{{ floatval($orbitRadius) }} 0 0"
                    @else
                    position="{{ implode(' ', array_map('floatval', (array)$position)) }}"
                    @endif
                    rotation="{{ implode(' ', array_map('floatval', (array)$rotation)) }}"
                    scale="{{ floatval($scale) }} {{ floatval($scale) }} {{ floatval($scale) }}"
                    animation-mixer="clip: {{ $animClip }}; loop: repeat; crossFadeDuration: 0.3"
                    ar-depth-fix>
                </a-entity>

            </a-entity>
            @endif
        </a-entity>
    </a-scene>

    <div id="scanning-overlay">
        <div class="scan-frame">
            <div class="scan-inner"></div>
            <div class="scan-line"></div>
        </div>
        <div class="scan-label">Arahkan ke Marker</div>
    </div>

    <div id="found-badge"><span class="dot"></span> Marker Terdeteksi</div>

    {{-- Badge muncul setelah object tertempel ke dunia --}}
    <div id="worldlock-badge">
        <span class="lock-icon">📌</span> Object Tertempel
    </div>

    {{-- Hint singkat cara jelajah --}}
    <div id="worldlock-hint">Putar HP ke segala arah untuk melihat object dari berbagai sisi</div>

    @if($orbitActive)
    <div id="orbit-badge" class="visible">⟳ Orbit Aktif</div>
    @else
    <div id="orbit-badge"></div>
    @endif

    <div id="clip-wrap">
        <span id="clip-label">Animasi</span>
        <select id="clip-select" onchange="switchClip(this.value)"></select>
    </div>

    <div id="ui-panel">
        <button class="btn-ui" onclick="window.history.back()">← Kembali</button>
        <button class="btn-ui" id="btn-rescan" style="display:none" onclick="arWorldReset()">⟳ Scan Ulang</button>
        <button class="btn-ui" id="btn-recalibrate" style="display:none" onclick="arRecalibrateGyro()" title="Kalibrasi ulang arah pandang">⊕ Kalibrasi</button>
        <button class="btn-ui primary" onclick="location.reload()">↺ Reset AR</button>
    </div>

    <script>
    // ── Config dari server ────────────────────────────────────────────────────
    const AR = {
        orbitActive: {{ $orbitActive ? 'true' : 'false' }},
        orbitSpeed:  {{ $orbitSpeed }},
        orbitRadius: {{ $orbitRadius }},
        orbitDir:    {{ $orbitDir }},
        animClip:    @json($animClip),
        hasModel:    {{ $modelUrl ? 'true' : 'false' }},
    };

    const sceneEl       = document.getElementById('ar-scene');
    const loadingEl     = document.getElementById('loading-overlay');
    const loadingStatus = document.getElementById('loading-status');
    const scanningEl    = document.getElementById('scanning-overlay');
    const foundBadge    = document.getElementById('found-badge');
    const orbitBadge    = document.getElementById('orbit-badge');
    const worldlockBadge = document.getElementById('worldlock-badge');
    const worldlockHint  = document.getElementById('worldlock-hint');
    const errorEl       = document.getElementById('error-overlay');
    const clipWrap      = document.getElementById('clip-wrap');
    const clipSelect    = document.getElementById('clip-select');
    const modelEl       = document.getElementById('model-container');

    let arReady = false, modelLoaded = false, isWorldLocked = false;

    function showError(title, msg) {
        loadingEl.style.display = 'none';
        scanningEl.classList.remove('visible');
        document.getElementById('error-title').textContent = title;
        document.getElementById('error-msg').textContent   = msg;
        errorEl.classList.add('show');
    }

    function hideLoading() {
        loadingEl.classList.add('fade-out');
        setTimeout(() => { loadingEl.style.display = 'none'; }, 500);
    }

    // Timeout 30 detik
    const loadTimeout = setTimeout(() => {
        if (!arReady) showError('Gagal Memuat', 'AR engine tidak merespons. Pastikan koneksi stabil dan browser mendukung kamera.');
    }, 600000);

    // ── MindAR ready ──────────────────────────────────────────────────────────
    sceneEl.addEventListener('arReady', () => {
        clearTimeout(loadTimeout);
        arReady = true;
        loadingStatus.textContent = 'Arahkan kamera ke gambar marker...';

        if (!AR.hasModel || modelLoaded) {
            hideLoading();
            scanningEl.classList.add('visible');
        }
    });

    // ── Model loaded ──────────────────────────────────────────────────────────
    if (modelEl) {
        modelEl.addEventListener('model-loaded', () => {
            modelLoaded = true;
            console.log('[AR] Model loaded ✓');

            // Populate clip selector dari animasi GLB
            try {
                const obj3d  = modelEl.getObject3D('mesh');
                const clips  = obj3d?.animations || [];
                if (clips.length > 1) {
                    clipSelect.innerHTML =
                        '<option value="*">Semua Animasi</option>' +
                        clips.map(c => `<option value="${c.name}"${AR.animClip === c.name ? ' selected' : ''}>${c.name}</option>`).join('');
                    clipWrap.classList.add('show');
                }
            } catch(e) { console.warn('[AR] clips error:', e); }

            if (arReady) {
                hideLoading();
                scanningEl.classList.add('visible');
            }

            if (AR.orbitActive) orbitBadge.classList.add('visible');

            // ── Paksa animation-mixer aktif sejak awal ─────────────────────
            // MindAR menyembunyikan entity sebelum marker ditemukan, yang
            // menyebabkan animation-mixer ter-pause saat init. Kita set ulang
            // atribut agar mixer tahu clip yang harus dimainkan.
            // Restart sesungguhnya terjadi di targetFound (lihat bawah).
            _restartMixer();
        });

        modelEl.addEventListener('model-error', () => {
            showError('Gagal Memuat Model', 'File 3D tidak dapat dimuat. Coba upload ulang model.');
        });
    } else if (!AR.hasModel) {
        modelLoaded = true;
    }

    // Helper: set ulang animation-mixer agar clip mulai dari awal
    function _restartMixer() {
        if (!modelEl) return;
        // Hapus dulu atribut → tambah lagi → mixer reset & mulai play
        modelEl.removeAttribute('animation-mixer');
        requestAnimationFrame(() => {
            modelEl.setAttribute('animation-mixer',
                `clip: ${AR.animClip || '*'}; loop: repeat; crossFadeDuration: 0.3; timeScale: 1`
            );
        });
    }

    // ── AR Error ──────────────────────────────────────────────────────────────
    sceneEl.addEventListener('arError', () => {
        const isHttps = location.protocol === 'https:';
        showError(
            'Kamera Tidak Bisa Diakses',
            isHttps ? 'Izinkan akses kamera di browser, lalu coba lagi.'
                    : 'AR membutuhkan HTTPS. Akses via ngrok atau domain HTTPS.'
        );
    });

    // ── Marker found / lost ───────────────────────────────────────────────────
    const targetEl = document.querySelector('[mindar-image-target]');
    targetEl.addEventListener('targetFound', () => {
        if (isWorldLocked) return; // sudah locked, abaikan
        scanningEl.classList.remove('visible');
        foundBadge.classList.add('visible');
        if (AR.orbitActive) orbitBadge.classList.add('visible');

        // MindAR me-resume visibility entity di sini, tapi animation-mixer
        // tidak otomatis resume. Restart mixer secara eksplisit.
        _restartMixer();
    });
    targetEl.addEventListener('targetLost', () => {
        if (isWorldLocked) return; // sudah locked, jangan tampilkan scanning lagi
        foundBadge.classList.remove('visible');
        orbitBadge.classList.remove('visible');
        scanningEl.classList.add('visible');
    });

    // ── World lock berhasil ───────────────────────────────────────────────────
    sceneEl.addEventListener('ar-world-locked', () => {
        isWorldLocked = true;

        foundBadge.classList.remove('visible');
        scanningEl.classList.remove('visible');
        worldlockBadge.classList.add('visible');

        document.getElementById('btn-rescan').style.display = 'flex';
        document.getElementById('btn-recalibrate').style.display = 'flex';

        worldlockHint.classList.add('visible');
        setTimeout(() => worldlockHint.classList.remove('visible'), 4000);

        // Pastikan animasi tetap jalan setelah world lock (reparent entity)
        _restartMixer();

        console.log('[AR] World lock UI updated ✓');
    });

    // ── Kalibrasi ulang gyro (reset baseline arah pandang) ───────────────────
    window.arRecalibrateGyro = () => {
        const camEl = sceneEl.querySelector('a-camera');
        if (!camEl) return;
        const gyroComp = camEl.components['ar-gyro-camera'];
        if (gyroComp) {
            gyroComp._qBase = null; // reset baseline → orientasi sekarang jadi titik nol baru
        }
    };

    // ── Switch clip ───────────────────────────────────────────────────────────
    function switchClip(clip) {
        if (!modelEl) return;
        AR.animClip = clip;
        _restartMixer();
    }

    // Prevent pinch zoom di mobile
    document.addEventListener('tosuchmove', e => {
        if (e.touches.length > 1) e.preventDefault();
    }, { passive: false });
    </script>
</body>
</html>