@extends('layouts.app')

@section('title', 'Edit Denah')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2>
            <i class="fas fa-edit me-2"></i>Edit Denah
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-2"></i>Informasi Denah</span>
                <a href="{{ route('floor-plans.print', $floorPlan) }}" class="btn btn-sm btn-light" target="_blank">
                    <i class="fas fa-print me-1"></i> Print
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('floor-plans.update', $floorPlan) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Denah</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $floorPlan->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Keterangan</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $floorPlan->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('floor-plans.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-map-marked-alt me-2"></i>Denah & Titik Pengukuran
            </div>
            <div class="card-body">
                @include('floor-plans.partials.editor', ['floorPlan' => $floorPlan])
            </div>
        </div>
    </div>
</div>
@endsection


