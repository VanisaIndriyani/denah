@extends('layouts.app')

@section('title', 'Detail Denah')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2>
            <i class="fas fa-map-marked-alt me-2"></i>Detail Denah
        </h2>
    </div>
    <div class="col-auto">
        <a href="{{ route('floor-plans.print', $floorPlan) }}" class="btn btn-success" target="_blank">
            <i class="fas fa-print me-1"></i> Print
        </a>
        <a href="{{ route('floor-plans.edit', $floorPlan) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Informasi Denah
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nama Denah</dt>
                    <dd class="col-sm-8">{{ $floorPlan->name }}</dd>

                    <dt class="col-sm-4">Jenis File</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-secondary">{{ strtoupper($floorPlan->file_type) }}</span>
                    </dd>

                    <dt class="col-sm-4">Keterangan</dt>
                    <dd class="col-sm-8">{{ $floorPlan->description ?: '-' }}</dd>

                    <dt class="col-sm-4">Jumlah Titik</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-info">{{ $floorPlan->points->count() }} titik</span>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-map me-2"></i>Denah & Titik Pengukuran
            </div>
            <div class="card-body">
                @include('floor-plans.partials.editor', ['floorPlan' => $floorPlan, 'readOnly' => true])
            </div>
        </div>
    </div>
</div>
@endsection


