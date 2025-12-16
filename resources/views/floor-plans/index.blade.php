@extends('layouts.app')

@section('title', 'Daftar Denah')

@section('content')
<div class="row mb-4">
    <div class="col-lg-8">
        <h2 class="mb-1">
            <i class="fas fa-map me-2"></i>Daftar Denah Ruangan
        </h2>
        <p class="text-muted mb-0">
            Kelola file denah hasil export AutoCAD dan titik pemetaan lingkungan kerja Anda.
        </p>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="{{ route('floor-plans.create') }}" class="btn btn-primary me-2 mb-2 mb-lg-0">
            <i class="fas fa-plus me-2"></i>Upload Denah Baru
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary mb-2 mb-lg-0">
            <i class="fas fa-home me-1"></i> Dashboard
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Semua Denah
            </h5>
            <small class="text-muted">
                Total <strong>{{ $floorPlans->total() }}</strong> denah |
                Menampilkan halaman {{ $floorPlans->currentPage() }} dari {{ $floorPlans->lastPage() }}
            </small>
        </div>
        @if($floorPlans->count() > 0)
            <span class="badge rounded-pill bg-success-subtle text-success fw-normal d-none d-md-inline">
                <i class="fas fa-circle me-1"></i>
                Aktif
            </span>
        @endif
    </div>
    <div class="card-body p-0">
        @if($floorPlans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="align-middle">
                        <tr>
                            <th style="width: 30%;">Denah</th>
                            <th style="width: 10%;">Jenis File</th>
                            <th style="width: 20%;">Tanggal Upload</th>
                            <th style="width: 15%;">Jumlah Titik</th>
                            <th style="width: 25%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($floorPlans as $floorPlan)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                             style="width:40px;height:40px;background-color:rgba(26,95,63,0.08);color:var(--primary-green);">
                                            <i class="fas fa-map-marked-alt"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $floorPlan->name }}</div>
                                            <small class="text-muted">
                                                Diunggah oleh {{ $floorPlan->user->name ?? 'Anda' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ strtoupper($floorPlan->file_type) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        {{ $floorPlan->created_at->format('d M Y') }}<br>
                                        <span class="text-muted">{{ $floorPlan->created_at->format('H:i') }} WIB</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $floorPlan->points->count() }} titik
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('floor-plans.edit', $floorPlan) }}" class="btn btn-primary">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="{{ route('floor-plans.print', $floorPlan) }}" class="btn btn-success" target="_blank">
                                            <i class="fas fa-print me-1"></i> Print
                                        </a>
                                    </div>
                                    <form action="{{ route('floor-plans.destroy', $floorPlan) }}"
                                          method="POST"
                                          class="d-inline ms-1"
                                          onsubmit="return confirm('Yakin ingin menghapus denah ini? Semua titik di dalamnya juga akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash me-1"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 d-flex justify-content-between align-items-center border-top bg-light">
                <small class="text-muted">
                    Menampilkan {{ $floorPlans->firstItem() }}–{{ $floorPlans->lastItem() }} dari {{ $floorPlans->total() }} denah
                </small>
                <div class="mb-0">
                    {{ $floorPlans->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-map fa-4x text-muted mb-3"></i>
                <h5 class="mb-2">Belum ada denah yang diupload</h5>
                <p class="text-muted mb-3">
                    Mulai dengan mengupload file denah hasil export dari AutoCAD (PNG/JPG/PDF),
                    lalu tambahkan titik pemetaan di atasnya.
                </p>
                <a href="{{ route('floor-plans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Upload Denah Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

