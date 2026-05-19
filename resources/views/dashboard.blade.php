@extends('layouts.app')

@section('title', 'Dashboard — Web AR Platform')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">Dashboard</h1>
        <p class="text-muted mb-0">Semua AR project Anda</p>
    </div>
    <a href="{{ route('ar.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Buat Project Baru
    </a>
</div>

@if ($projects->isEmpty())
    {{-- Empty state --}}
    <div class="text-center py-5">
        <div class="mb-4" style="font-size: 5rem; opacity: 0.15;">
            <i class="bi bi-badge-ar"></i>
        </div>
        <h4 class="text-muted">Belum ada project AR</h4>
        <p class="text-muted mb-4">Mulai buat pengalaman AR pertama Anda!</p>
        <a href="{{ route('ar.create') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-lg me-2"></i>Buat Project AR
        </a>
    </div>
@else
    <div class="row g-3">
        @foreach ($projects as $project)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                {{-- Preview marker thumbnail --}}
                <div class="card-img-top overflow-hidden" style="height: 160px; background: #f1f5f9;">
                    <img src="{{ $project->marker->image_url }}"
                         alt="Marker"
                         class="w-100 h-100"
                         style="object-fit: cover;">
                </div>

                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            @if ($project->type === 'template')
                                <span class="badge bg-primary" style="font-size: 0.7rem;">
                                    <i class="bi bi-collection me-1"></i>Template
                                </span>
                                @if ($project->template)
                                    <span class="badge bg-light text-dark ms-1" style="font-size: 0.7rem;">
                                        {{ $project->template->name }}
                                    </span>
                                @endif
                            @elseif ($project->type === 'gltf')
                                <span class="badge bg-info" style="font-size: 0.7rem;">
                                    <i class="bi bi-cube me-1"></i>GLB/GLTF
                                </span>
                            @elseif ($project->type === 'blend')
                                <span class="badge bg-success" style="font-size: 0.7rem;">
                                    <i class="bi bi-file-earmark me-1"></i>Blend
                                </span>
                            @else
                                <span class="badge bg-secondary" style="font-size: 0.7rem;">
                                    {{ $project->type }}
                                </span>
                            @endif
                        </div>
                        {{-- Marker status badge --}}
                        @php $status = $project->marker->status; @endphp
                        <span class="badge badge-{{ $status }}">
                            @if ($status === 'ready') <i class="bi bi-check-circle me-1"></i>
                            @elseif ($status === 'processing') <i class="bi bi-hourglass-split me-1"></i>
                            @else <i class="bi bi-x-circle me-1"></i>
                            @endif
                            {{ ucfirst($status) }}
                        </span>
                    </div>

                    <p class="text-muted small mb-3">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ $project->created_at->diffForHumans() }}
                    </p>

                    {{-- Config preview jika ada --}}
                    @if ($project->config && count($project->config))
                        <div class="small text-muted mb-3">
                            @foreach (array_slice($project->config, 0, 2) as $key => $val)
                                <div><strong>{{ ucfirst($key) }}:</strong> {{ $val }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-transparent border-top-0 pb-3 px-3">
                    <div class="d-flex gap-2">
                        <a href="{{ route('ar.view', $project->id) }}"
                           class="btn btn-outline-primary btn-sm flex-grow-1"
                           target="_blank">
                            <i class="bi bi-badge-ar me-1"></i>Buka AR
                        </a>
                        <a href="{{ route('ar.result', $project->id) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-qr-code"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $projects->links() }}
    </div>
@endif
@endsection
