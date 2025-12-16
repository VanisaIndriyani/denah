@extends('layouts.app')

@section('title', 'Edit Denah')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2>
            <i class="fas fa-edit me-2"></i>Edit Denah
        </h2>
    </div>
    <div class="col-auto">
        <a href="{{ route('floor-plans.print', $floorPlan) }}" class="btn btn-success" target="_blank">
            <i class="fas fa-print me-1"></i> Print
        </a>
        <a href="{{ route('floor-plans.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informasi Denah
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('floor-plans.update', $floorPlan) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nama Denah</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $floorPlan->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Keterangan</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Masukkan keterangan denah (opsional)">{{ old('description', $floorPlan->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-map-marked-alt me-2"></i>Denah & Titik Pengukuran
                </h5>
            </div>
            <div class="card-body p-0">
                @include('floor-plans.partials.editor', ['floorPlan' => $floorPlan])
            </div>
        </div>

        {{-- Tabel Hasil Pengukuran --}}
        @if($floorPlan->points->where('type', 'area')->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Hasil Pengukuran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 20%;">Jenis Pengukuran Lingkungan Kerja</th>
                                    <th style="width: 15%;">Kategori</th>
                                    <th style="width: 15%;">Posisi</th>
                                    <th style="width: 25%;">Hasil Pengukuran</th>
                                    <th style="width: 20%;">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $measurementIndex = 0;
                                @endphp
                                @foreach($floorPlan->points->where('type', 'area') as $area)
                                    @php
                                        $room = $area->room;
                                        $measurementIndex++;
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-bold">{{ $measurementIndex }}</td>
                                        <td>
                                            @php
                                                $jenisPengukuran = [];
                                                if ($area->measurements && is_array($area->measurements)) {
                                                    foreach ($area->measurements as $measurement) {
                                                        $paramName = $measurement['parameter'];
                                                        if ($paramName === 'pencahayaan') {
                                                            $jenisPengukuran[] = 'Pencahayaan';
                                                        } elseif ($paramName === 'debu_total') {
                                                            $jenisPengukuran[] = 'Debu Total';
                                                        } elseif (strpos($paramName, 'kudr_') === 0) {
                                                            $jenisPengukuran[] = 'Kualitas Udara Dalam Ruangan';
                                                        } else {
                                                            $jenisPengukuran[] = ucfirst(str_replace('_', ' ', $paramName));
                                                        }
                                                    }
                                                } elseif ($area->parameter) {
                                                    if ($area->parameter === 'pencahayaan') {
                                                        $jenisPengukuran[] = 'Pencahayaan';
                                                    } elseif ($area->parameter === 'debu_total') {
                                                        $jenisPengukuran[] = 'Debu Total';
                                                    } elseif (strpos($area->parameter, 'kudr_') === 0) {
                                                        $jenisPengukuran[] = 'Kualitas Udara Dalam Ruangan';
                                                    } else {
                                                        $jenisPengukuran[] = ucfirst(str_replace('_', ' ', $area->parameter));
                                                    }
                                                } else {
                                                    $jenisPengukuran[] = 'Pengukuran Lingkungan Kerja';
                                                }
                                            @endphp
                                            <small>{{ implode(', ', array_unique($jenisPengukuran)) ?: 'Pengukuran Lingkungan Kerja' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $area->category === 'diatas_nab' ? 'bg-danger' : 'bg-success' }} fs-6">
                                                {{ $area->category === 'diatas_nab' ? 'Di atas NAB' : 'Di bawah NAB' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($area->coordinates && is_array($area->coordinates))
                                                @php
                                                    $coords = collect($area->coordinates);
                                                    $centerX = $coords->avg('x');
                                                    $centerY = $coords->avg('y');
                                                @endphp
                                                <small>{{ number_format($centerX, 2) }}%, {{ number_format($centerY, 2) }}%</small>
                                            @else
                                                <small>{{ $area->x }}%, {{ $area->y }}%</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $measurementTexts = [];
                                                if ($area->measurements && is_array($area->measurements)) {
                                                    foreach ($area->measurements as $measurement) {
                                                        $value = number_format($measurement['value'], $measurement['parameter'] === 'pencahayaan' ? 0 : 2);
                                                        $measurementTexts[] = '<strong>' . $value . '</strong> ' . $measurement['unit'];
                                                    }
                                                } elseif ($area->value && $area->unit) {
                                                    $value = number_format($area->value, $area->parameter === 'pencahayaan' ? 0 : 2);
                                                    $measurementTexts[] = '<strong>' . $value . '</strong> ' . $area->unit;
                                                }
                                            @endphp
                                            @if(count($measurementTexts) > 0)
                                                <small>{!! implode(', ', $measurementTexts) !!}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                // Ambil notes tanpa JSON measurements jika ada
                                                $notes = $area->notes ?? '';
                                                if (strpos($notes, '|') !== false && json_decode(substr($notes, strrpos($notes, '|') + 1))) {
                                                    $notes = trim(substr($notes, 0, strrpos($notes, '|')));
                                                }
                                            @endphp
                                            <small>{{ $notes ?: ($room ? $room->name : '-') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Belum ada hasil pengukuran. Silakan tambahkan area pengukuran pada denah di atas.</p>
                </div>
            </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Statistik Denah
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Area</span>
                        <span class="badge bg-primary fs-6">{{ $floorPlan->points->where('type', 'area')->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Titik</span>
                        <span class="badge bg-info fs-6">{{ $floorPlan->points->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Ruangan</span>
                        <span class="badge bg-success fs-6">{{ $floorPlan->rooms->count() }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Memenuhi NAB</span>
                        <span class="badge bg-success fs-6">{{ $floorPlan->points->where('type', 'area')->where('category', 'dibawah_nab')->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Tidak Memenuhi NAB</span>
                        <span class="badge bg-danger fs-6">{{ $floorPlan->points->where('type', 'area')->where('category', 'diatas_nab')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informasi File
                </h5>
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt class="text-muted small">Jenis File</dt>
                    <dd class="mb-2">
                        <span class="badge bg-secondary">{{ strtoupper($floorPlan->file_type) }}</span>
                    </dd>
                    <dt class="text-muted small">Dibuat</dt>
                    <dd class="mb-2">
                        <small>{{ $floorPlan->created_at->format('d M Y, H:i') }}</small>
                    </dd>
                    <dt class="text-muted small">Diperbarui</dt>
                    <dd>
                        <small>{{ $floorPlan->updated_at->format('d M Y, H:i') }}</small>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection


