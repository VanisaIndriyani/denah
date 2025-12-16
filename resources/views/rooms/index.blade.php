@extends('layouts.app')

@section('title', 'Kelola Ruangan')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2>
            <i class="fas fa-door-open me-2"></i>Kelola Ruangan
        </h2>
        <p class="text-muted mb-0">Kelola data ruangan untuk setiap denah</p>
    </div>
    <div class="col-auto">
        <a href="{{ route('rooms.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Ruangan
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>Daftar Ruangan
        </h5>
    </div>
    <div class="card-body">
        @if($rooms->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Ruangan</th>
                            <th>Denah</th>
                            <th>Keterangan</th>
                            <th>Jumlah Titik</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms as $room)
                            <tr>
                                <td>
                                    <i class="fas fa-door-open me-2"></i>
                                    <strong>{{ $room->name }}</strong>
                                </td>
                                <td>
                                    <a href="{{ route('floor-plans.edit', $room->floorPlan) }}" class="text-decoration-none">
                                        <i class="fas fa-map-marked-alt me-1"></i>
                                        {{ $room->floorPlan->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $room->description ?: '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $room->points->count() }} titik</span>
                                </td>
                                <td>
                                    <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus ruangan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $rooms->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-door-open fa-4x text-muted mb-3"></i>
                <p class="text-muted">Belum ada ruangan yang ditambahkan.</p>
                <a href="{{ route('rooms.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Ruangan Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

