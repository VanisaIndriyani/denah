@extends('layouts.app')

@section('title', 'Edit Ruangan')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2>
            <i class="fas fa-edit me-2"></i>Edit Ruangan
        </h2>
    </div>
    <div class="col-auto">
        <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-door-open me-2"></i>Form Edit Ruangan
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('rooms.update', $room) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag me-1"></i>Nama Ruangan <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $room->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="floor_plan_id" class="form-label">
                            <i class="fas fa-map me-1"></i>Denah <span class="text-danger">*</span>
                        </label>
                        <select name="floor_plan_id" 
                                id="floor_plan_id" 
                                class="form-select @error('floor_plan_id') is-invalid @enderror" 
                                required>
                            <option value="">-- Pilih Denah --</option>
                            @foreach($floorPlans as $fp)
                                <option value="{{ $fp->id }}" {{ old('floor_plan_id', $room->floor_plan_id) == $fp->id ? 'selected' : '' }}>
                                    {{ $fp->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('floor_plan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-info-circle me-1"></i>Keterangan (opsional)
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3" 
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $room->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">
                        <i class="fas fa-chart-line me-2"></i>Jumlah Titik Pengukuran
                    </h6>
                    <p class="text-muted small mb-3">Tentukan jumlah titik pengukuran untuk setiap jenis parameter di ruangan ini. Simbol akan muncul di denah saat dicetak.</p>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="lighting_points" class="form-label">
                                <span style="font-size: 1.2em;">❌</span> Pengukuran Pencahayaan
                            </label>
                            <input type="number" 
                                   name="lighting_points" 
                                   id="lighting_points" 
                                   class="form-control @error('lighting_points') is-invalid @enderror" 
                                   value="{{ old('lighting_points', $room->lighting_points ?? 0) }}" 
                                   min="0"
                                   placeholder="0">
                            @error('lighting_points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Jumlah titik pengukuran pencahayaan</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="dust_points" class="form-label">
                                <span style="font-size: 1.2em;">⭕</span> Debu Total
                            </label>
                            <input type="number" 
                                   name="dust_points" 
                                   id="dust_points" 
                                   class="form-control @error('dust_points') is-invalid @enderror" 
                                   value="{{ old('dust_points', $room->dust_points ?? 0) }}" 
                                   min="0"
                                   placeholder="0">
                            @error('dust_points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Jumlah titik pengukuran debu total</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="air_quality_points" class="form-label">
                                <span style="font-size: 1.2em;">🔺</span> Kualitas Udara Dalam Ruangan
                            </label>
                            <input type="number" 
                                   name="air_quality_points" 
                                   id="air_quality_points" 
                                   class="form-control @error('air_quality_points') is-invalid @enderror" 
                                   value="{{ old('air_quality_points', $room->air_quality_points ?? 0) }}" 
                                   min="0"
                                   placeholder="0">
                            @error('air_quality_points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Jumlah titik pengukuran kualitas udara</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

