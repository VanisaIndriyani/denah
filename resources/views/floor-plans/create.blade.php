@extends('layouts.app')

@section('title', 'Upload Denah Baru')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2>
            <i class="fas fa-plus me-2"></i>Upload Denah Baru
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-upload me-2"></i>Form Upload Denah
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('floor-plans.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Denah</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">File Denah (PNG/JPG)</label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".png,.jpg,.jpeg,.pdf" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Upload hasil export dari AutoCAD (PNG/JPG), maksimal 10 MB.</small>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Keterangan (opsional)</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('floor-plans.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


