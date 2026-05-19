@extends('layouts.app')

@section('title', 'Template AR')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold">Template AR</h1>
        <p class="text-muted mb-0">Template yang tersedia untuk AR project Anda</p>
    </div>
    <a href="{{ route('ar.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Buat AR Baru
    </a>
</div>

@if ($templates->isEmpty())
    <div class="text-center py-5">
        <div style="font-size: 4rem; opacity: 0.15;"><i class="bi bi-collection"></i></div>
        <h4 class="text-muted mt-2">Belum ada template</h4>
        <p class="text-muted">Jalankan <code>php artisan db:seed --class=TemplateSeeder</code> untuk menambahkan template.</p>
    </div>
@else
    <div class="row g-3">
        @foreach ($templates as $template)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                {{-- Thumbnail --}}
                <div class="card-img-top overflow-hidden d-flex align-items-center justify-content-center bg-light"
                     style="height: 180px;">
                    @if ($template->thumbnail_url)
                        <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}"
                             class="w-100 h-100" style="object-fit: cover;">
                    @else
                        <i class="bi bi-box text-muted" style="font-size: 4rem;"></i>
                    @endif
                </div>

                <div class="card-body">
                    <h5 class="card-title fw-bold mb-1">{{ $template->name }}</h5>

                    {{-- Config schema preview --}}
                    @if ($template->config_schema)
                        <p class="text-muted small mb-2">
                            <i class="bi bi-input-cursor-text me-1"></i>
                            Input: {{ collect($template->config_schema)->pluck('label')->join(', ') }}
                        </p>
                    @endif

                    {{-- Placeholders --}}
                    @if ($template->placeholders)
                        <p class="text-muted small mb-0">
                            <i class="bi bi-braces me-1"></i>
                            {{ count($template->placeholders) }} placeholder teks
                        </p>
                    @endif
                </div>

                <div class="card-footer bg-transparent border-top-0 px-3 pb-3">
                    <a href="{{ route('ar.create') }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-magic me-1"></i>Gunakan Template Ini
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
