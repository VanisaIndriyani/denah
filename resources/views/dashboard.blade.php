@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex align-items-center mb-2">
            <div class="me-3">
                <span class="badge bg-success rounded-pill px-3 py-2">
                    <i class="fas fa-circle me-1"></i> Aktif
                </span>
            </div>
            <div>
                <h2 class="mb-0">
                    <i class="fas fa-home me-2"></i>Dashboard
                </h2>
                <p class="text-muted mb-0">Selamat datang, <strong>{{ Auth::user()->name }}</strong>. Kelola pemetaan lingkungan kerja Anda di sini.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="{{ route('floor-plans.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Upload Denah Baru
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                     style="width:52px;height:52px;background-color:rgba(26,95,63,0.1);color:var(--primary-green);">
                    <i class="fas fa-map fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Denah</div>
                    <div class="h4 mb-0">{{ $floorPlans->total() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                     style="width:52px;height:52px;background-color:rgba(108,117,125,0.1);color:#6c757d;">
                    <i class="fas fa-door-open fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Ruangan</div>
                    <div class="h4 mb-0">{{ $totalRooms }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                     style="width:52px;height:52px;background-color:rgba(25,135,84,0.1);color:#198754;">
                    <i class="fas fa-circle-notch fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Titik</div>
                    <div class="h4 mb-0">
                        {{ $floorPlans->sum(fn($fp) => $fp->points->count()) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                     style="width:52px;height:52px;background-color:rgba(220,53,69,0.1);color:#dc3545;">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Titik Di atas NAB</div>
                    <div class="h4 mb-0">
                        {{ $floorPlans->sum(fn($fp) => $fp->points->where('category', 'diatas_nab')->count()) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col">
        <h4 class="mb-0">
            <i class="fas fa-map-marked-alt me-2"></i>Denah Ruangan
        </h4>
        <p class="text-muted mb-0">Klik pada denah untuk mengelola titik pengukuran</p>
    </div>
</div>

@if($floorPlans->count() > 0)
    <div class="row g-4">
        @foreach($floorPlans as $floorPlan)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0" style="transition: transform 0.2s, box-shadow 0.2s;">
                    <div class="card-header bg-white border-bottom p-0" style="position: relative; overflow: hidden;">
                        @if(in_array(strtolower($floorPlan->file_type), ['png','jpg','jpeg']))
                            <div style="position: relative; width: 100%; padding-top: 60%; background: #f8f9fa;">
                                <img src="{{ asset('storage/'.$floorPlan->file_path) }}" 
                                     alt="{{ $floorPlan->name }}"
                                     style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; cursor: pointer;"
                                     onclick="window.location.href='{{ route('floor-plans.edit', $floorPlan) }}'">
                                
                                <!-- Overlay untuk titik -->
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                                    <svg style="width: 100%; height: 100%;">
                                        @foreach($floorPlan->points as $point)
                                            @if($point->type === 'area' && $point->coordinates && is_array($point->coordinates))
                                                <polygon points="{{ collect($point->coordinates)->map(function($coord) {
                                                    return ($coord['x'] ?? 0) . ',' . ($coord['y'] ?? 0);
                                                })->join(' ') }}"
                                                         style="fill: {{ $point->category === 'diatas_nab' ? 'rgba(220, 53, 69, 0.25)' : 'rgba(25, 135, 84, 0.25)' }}; 
                                                                stroke: {{ $point->category === 'diatas_nab' ? '#dc3545' : '#198754' }}; 
                                                                stroke-width: 1.5;">
                                                </polygon>
                                            @endif
                                        @endforeach
                                    </svg>
                                    @foreach($floorPlan->points as $point)
                                        @if($point->type === 'point')
                                            @php
                                                // Filter: jangan tampilkan point measurements yang duplikat dengan area
                                                // Cek apakah ada area dengan koordinat yang sama (dalam toleransi 0.1%)
                                                $isDuplicate = false;
                                                $tolerance = 0.1; // toleransi 0.1%
                                                
                                                foreach ($floorPlan->points as $areaPoint) {
                                                    if ($areaPoint->type === 'area' && 
                                                        abs($areaPoint->x - $point->x) < $tolerance && 
                                                        abs($areaPoint->y - $point->y) < $tolerance) {
                                                        $isDuplicate = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if(!$isDuplicate)
                                                <div style="position: absolute; 
                                                            left: {{ $point->x }}%; 
                                                            top: {{ $point->y }}%; 
                                                            width: 8px; 
                                                            height: 8px; 
                                                            border-radius: 50%; 
                                                            background-color: {{ $point->category === 'diatas_nab' ? '#dc3545' : '#198754' }}; 
                                                            border: 1.5px solid white; 
                                                            transform: translate(-50%, -50%); 
                                                            box-shadow: 0 0 2px rgba(0,0,0,0.3);">
                                                </div>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                                
                                <!-- Badge jumlah titik -->
                                @if($floorPlan->points->count() > 0)
                                    <div style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $floorPlan->points->count() }} titik
                                    </div>
                                @endif
                            </div>
                        @else
                            <div style="padding: 60px 20px; background: #f8f9fa; text-align: center;">
                                <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                <p class="text-muted small mb-0">File PDF</p>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <h6 class="card-title mb-2" style="font-weight: 600; color: var(--primary-green);">
                            <i class="fas fa-map-marked-alt me-2"></i>{{ Str::limit($floorPlan->name, 40) }}
                        </h6>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2">{{ strtoupper($floorPlan->file_type) }}</span>
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>{{ $floorPlan->created_at->format('d/m/Y') }}
                            </small>
                        </div>
                        @if($floorPlan->description)
                            <p class="text-muted small mb-2" style="font-size: 0.85rem;">
                                {{ Str::limit($floorPlan->description, 60) }}
                            </p>
                        @endif
                        
                        <!-- Statistik titik -->
                        @if($floorPlan->points->count() > 0)
                            <div class="d-flex gap-2 mb-3">
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ $floorPlan->points->where('category', 'diatas_nab')->count() }} Di atas NAB
                                </span>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $floorPlan->points->where('category', 'dibawah_nab')->count() }} Di bawah NAB
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('floor-plans.edit', $floorPlan) }}" class="btn btn-sm btn-primary flex-fill">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <a href="{{ route('floor-plans.print', $floorPlan) }}" class="btn btn-sm btn-success" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                            <form action="{{ route('floor-plans.destroy', $floorPlan) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus denah ini? Semua titik yang terkait juga akan dihapus.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="mt-4">
        {{ $floorPlans->links() }}
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-map fa-4x text-muted mb-3"></i>
            <h5 class="text-muted mb-2">Belum ada denah yang diupload</h5>
            <p class="text-muted mb-4">Mulai dengan mengupload denah ruangan pertama Anda</p>
            <a href="{{ route('floor-plans.create') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>Upload Denah Pertama
            </a>
        </div>
    </div>
@endif

@push('styles')
<style>
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush
@endsection

