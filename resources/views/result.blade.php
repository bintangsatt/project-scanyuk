@extends('layouts.app')

@section('title', 'Project AR Berhasil Dibuat!')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="text-center mb-4">
        <div style="font-size: 4rem;">🎉</div>
        <h2 class="fw-bold">Project AR Berhasil Dibuat!</h2>
        <p class="text-muted">Scan QR code di bawah untuk membuka pengalaman AR</p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 text-center">
            {{-- QR Code SVG --}}
            <div class="qr-wrapper d-inline-block p-3 bg-white rounded shadow-sm mb-3">
                {!! $qrCode !!}
            </div>

            <p class="text-muted small mb-1">Scan QR code dengan kamera smartphone</p>
            <p class="fw-semibold mb-3">atau buka link berikut:</p>

            {{-- AR Link --}}
            <div class="input-group mb-3">
                <input type="text" class="form-control text-center" id="ar-link-input"
                       value="{{ $arUrl }}" readonly>
                <button class="btn btn-outline-primary" onclick="copyLink()" title="Salin link">
                    <i class="bi bi-clipboard" id="copy-icon"></i>
                </button>
            </div>

            <a href="{{ $arUrl }}" target="_blank" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-badge-ar me-2"></i>Buka AR Viewer
            </a>
        </div>
    </div>

    {{-- Detail project --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-bold">Detail Project</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-borderless mb-0">
                <tbody>
                    <tr>
                        <td class="text-muted ps-3" style="width: 140px;">ID Project</td>
                        <td><code>#{{ $project->id }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Tipe</td>
                        <td>
                            @if ($project->type === 'template')
                                <span class="badge bg-primary">Template</span>
                                @if ($project->template)
                                    <span class="ms-1">{{ $project->template->name }}</span>
                                @endif
                            @elseif ($project->type === 'gltf')
                                <span class="badge bg-info">GLB/GLTF</span>
                                <span class="ms-1 text-muted small">Model 3D upload</span>
                            @elseif ($project->type === 'blend')
                                <span class="badge bg-success">Blend (Converted)</span>
                                <span class="ms-1 text-muted small">Model Blend → GLB</span>
                            @else
                                <span class="badge bg-warning text-dark">Tipe Unknown</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Marker</td>
                        <td>
                            <img src="{{ $project->marker->image_url }}"
                                 alt="Marker" style="height: 50px; border-radius: 6px;">
                        </td>
                    </tr>
                    @if ($project->config && count($project->config))
                    <tr>
                        <td class="text-muted ps-3 align-top">Konfigurasi</td>
                        <td>
                            @foreach ($project->config as $key => $val)
                                <div><strong>{{ ucfirst($key) }}:</strong> {{ $val }}</div>
                            @endforeach
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted ps-3">Dibuat</td>
                        <td>{{ $project->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Petunjuk penggunaan --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-bold">Cara Menggunakan AR</h6>
        </div>
        <div class="card-body">
            <ol class="mb-0">
                <li class="mb-2">
                    <strong>Scan QR code</strong> di atas menggunakan kamera smartphone atau
                    buka link AR di browser
                </li>
                <li class="mb-2">
                    <strong>Izinkan akses kamera</strong> saat browser meminta permission
                </li>
                <li class="mb-2">
                    <strong>Cetak atau tampilkan</strong> gambar marker di layar lain
                </li>
                <li>
                    <strong>Arahkan kamera</strong> ke gambar marker →
                    <strong>objek 3D akan muncul!</strong> ✨
                </li>
            </ol>
        </div>
    </div>

    <div class="d-flex gap-3 justify-content-center">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-grid me-1"></i>Dashboard
        </a>
        <a href="{{ route('ar.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Buat Project Baru
        </a>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
function copyLink() {
    const input = document.getElementById('ar-link-input');
    navigator.clipboard.writeText(input.value).then(() => {
        const icon = document.getElementById('copy-icon');
        icon.className = 'bi bi-clipboard-check text-success';
        setTimeout(() => { icon.className = 'bi bi-clipboard'; }, 2000);
    });
}
</script>
@endpush
