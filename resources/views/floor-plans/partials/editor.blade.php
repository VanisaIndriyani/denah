@php
    $readOnly = $readOnly ?? false;
@endphp

<style>
    .floorplan-wrapper {
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background-color: #fff;
    }
    .floorplan-image {
        width: 100%;
        display: block;
    }
    .floorplan-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    .floorplan-point {
        position: absolute;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        border: 2px solid #fff;
        box-shadow: 0 0 4px rgba(0,0,0,0.4);
        cursor: pointer;
        pointer-events: auto;
    }
    .floorplan-point.diatas_nab {
        background-color: #dc3545; /* merah */
    }
    .floorplan-point.dibawah_nab {
        background-color: #198754; /* hijau */
    }
    .floorplan-area {
        position: absolute;
        pointer-events: auto;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .floorplan-area:hover {
        stroke-width: 2 !important;
        opacity: 0.8;
    }
    .floorplan-area.diatas_nab {
        background-color: rgba(220, 53, 69, 0.3); /* merah transparan */
        border: 2px solid #dc3545;
    }
    .floorplan-area.dibawah_nab {
        background-color: rgba(25, 135, 84, 0.3); /* hijau transparan */
        border: 2px solid #198754;
    }
    .floorplan-area polygon {
        fill: inherit;
        stroke: inherit;
        stroke-width: 1;
    }
    .legend-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background-color: #f1f3f5;
        margin-right: 8px;
        font-size: 0.875rem;
    }
    .legend-color {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .measurement-symbol {
        position: absolute;
        font-size: 6px;
        line-height: 1;
        transform: translate(-50%, -50%);
        z-index: 20;
        text-shadow: 0.5px 0.5px 1px rgba(255,255,255,1), -0.5px -0.5px 1px rgba(255,255,255,1), 0 0 2px rgba(255,255,255,1);
        font-weight: normal;
        pointer-events: none;
    }
    .measurement-symbol.air-quality {
        font-size: 5px;
    }
    .measurement-symbol-group {
        display: inline-block;
        margin: 0 2px;
    }
</style>

<div class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="legend-pill">
                <span class="legend-color" style="background-color:#dc3545"></span>
                Di atas NAB
            </span>
            <span class="legend-pill">
                <span class="legend-color" style="background-color:#198754"></span>
                Di bawah NAB
            </span>
            <span class="legend-pill">
                <span style="font-size: 16px;">❌</span> Pencahayaan
            </span>
            <span class="legend-pill">
                <span style="font-size: 16px;">⭕</span> Debu Total
            </span>
            <span class="legend-pill">
                <span style="font-size: 16px;">🔺</span> Kualitas Udara
            </span>
        </div>
    </div>
    @unless($readOnly)
        <div class="alert alert-info mb-3">
            <br><strong class="text-warning">⚠ Penting:</strong> Pilih ruangan saat menyimpan area dan pastikan jumlah titik pengukuran sudah diisi di form ruangan agar simbol (❌ ⭕ 🔺) muncul.
            <br><strong class="text-danger">🗑️ Hapus Area:</strong> Double-click pada area yang sudah dibuat untuk menghapusnya jika ada kesalahan input.
        </div>
    @endunless
</div>

<div class="floorplan-wrapper mb-3" id="floorplan-wrapper-{{ $floorPlan->id }}">
    @if(in_array(strtolower($floorPlan->file_type), ['png','jpg','jpeg']))
        <img src="{{ asset('storage/'.$floorPlan->file_path) }}"
             alt="{{ $floorPlan->name }}"
             class="floorplan-image"
             id="floorplan-image-{{ $floorPlan->id }}">
    @else
        <div class="p-4 text-center text-muted">
            <i class="fas fa-file-pdf fa-3x mb-2 text-danger"></i>
            <p>File PDF tidak bisa diberi titik langsung di sini. Silakan gunakan versi gambar (PNG/JPG) untuk pemetaan titik.</p>
        </div>
    @endif

    <div class="floorplan-overlay" id="floorplan-overlay-{{ $floorPlan->id }}">
        <svg style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;" viewBox="0 0 100 100" preserveAspectRatio="none">
            @foreach($floorPlan->points as $point)
                @if($point->type === 'area' && $point->coordinates && is_array($point->coordinates))
                    <polygon class="floorplan-area {{ $point->category }}"
                             data-id="{{ $point->id }}"
                             points="{{ collect($point->coordinates)->map(function($coord) {
                                 return ($coord['x'] ?? 0) . ',' . ($coord['y'] ?? 0);
                             })->join(' ') }}"
                             style="fill: {{ $point->category === 'diatas_nab' ? 'rgba(220, 53, 69, 0.08)' : 'rgba(25, 135, 84, 0.08)' }}; 
                                    stroke: {{ $point->category === 'diatas_nab' ? '#dc3545' : '#198754' }}; 
                                    stroke-width: 1; 
                                    pointer-events: auto;"
                             title="{{ $point->notes }}">
                    </polygon>
                @endif
            @endforeach
        </svg>
        
        {{-- Tampilkan simbol pengukuran untuk setiap ruangan --}}
        @foreach($floorPlan->rooms as $room)
            @php
                // Cari area yang terkait dengan ruangan ini - cek semua area dengan room_id
                $roomArea = $floorPlan->points->where('room_id', $room->id)->where('type', 'area')->first();
                
                // Jika tidak ditemukan, coba cari berdasarkan nama ruangan di notes
                if (!$roomArea) {
                    $roomArea = $floorPlan->points->where('type', 'area')
                        ->filter(function($point) use ($room) {
                            return $point->notes && stripos($point->notes, $room->name) !== false;
                        })
                        ->first();
                }
                
                // Hitung posisi untuk menampilkan simbol - HANYA jika ada area
                $symbolX = null;
                $symbolY = null;
                
                if ($roomArea && $roomArea->coordinates && is_array($roomArea->coordinates) && count($roomArea->coordinates) > 0) {
                    // Hitung center dari area polygon
                    $coords = collect($roomArea->coordinates);
                    $symbolX = $coords->avg('x');
                    $symbolY = $coords->avg('y');
                }
                
                // Hitung total simbol yang akan ditampilkan
                $totalSymbols = (int)($room->lighting_points ?? 0) + (int)($room->dust_points ?? 0) + (int)($room->air_quality_points ?? 0);
            @endphp
            
            {{-- Debug: Tampilkan info di console untuk troubleshooting --}}
            <script>
                console.log('Room: "{{ $room->name }}"', {
                    roomId: {{ $room->id }},
                    lighting: {{ $room->lighting_points ?? 0 }},
                    dust: {{ $room->dust_points ?? 0 }},
                    air_quality: {{ $room->air_quality_points ?? 0 }},
                    totalSymbols: {{ $totalSymbols }},
                    hasArea: {{ $roomArea ? 'true' : 'false' }},
                    areaRoomId: {{ $roomArea ? ($roomArea->room_id ?? 'null') : 'null' }},
                    symbolX: {{ $symbolX !== null ? $symbolX : 'null' }},
                    symbolY: {{ $symbolY !== null ? $symbolY : 'null' }},
                    willShow: {{ ($symbolX !== null && $symbolY !== null && $totalSymbols > 0) ? 'true' : 'false' }}
                });
            </script>
            
            {{-- Hanya tampilkan simbol jika ada area dan ada jumlah titik pengukuran --}}
            @if($symbolX !== null && $symbolY !== null && $totalSymbols > 0)
                @php
                    // Spread symbols dalam grid kecil jika ada banyak simbol
                    $symbolIndex = 0;
                    $offsetPerSymbol = 1.5; // offset dalam persen untuk setiap simbol
                @endphp
                
                {{-- Simbol Pengukuran Pencahayaan (❌) --}}
                @if($room->lighting_points > 0)
                    @for($i = 0; $i < $room->lighting_points; $i++)
                        @php
                            $offsetX = ($symbolIndex % 3 - 1) * $offsetPerSymbol;
                            $offsetY = floor($symbolIndex / 3) * $offsetPerSymbol;
                            $symbolIndex++;
                        @endphp
                        <div class="measurement-symbol" style="left: {{ $symbolX + $offsetX }}%; top: {{ $symbolY + $offsetY }}%;">
                            <span>❌</span>
                        </div>
                    @endfor
                @endif
                
                {{-- Simbol Debu Total (⭕) --}}
                @if($room->dust_points > 0)
                    @for($i = 0; $i < $room->dust_points; $i++)
                        @php
                            $offsetX = ($symbolIndex % 3 - 1) * $offsetPerSymbol;
                            $offsetY = floor($symbolIndex / 3) * $offsetPerSymbol;
                            $symbolIndex++;
                        @endphp
                        <div class="measurement-symbol" style="left: {{ $symbolX + $offsetX }}%; top: {{ $symbolY + $offsetY }}%;">
                            <span>⭕</span>
                        </div>
                    @endfor
                @endif
                
                {{-- Simbol Kualitas Udara (🔺) --}}
                @if($room->air_quality_points > 0)
                    @for($i = 0; $i < $room->air_quality_points; $i++)
                        @php
                            $offsetX = ($symbolIndex % 3 - 1) * $offsetPerSymbol;
                            $offsetY = floor($symbolIndex / 3) * $offsetPerSymbol;
                            $symbolIndex++;
                        @endphp
                        <div class="measurement-symbol air-quality" style="left: {{ $symbolX + $offsetX }}%; top: {{ $symbolY + $offsetY }}%;">
                            <span>🔺</span>
                        </div>
                    @endfor
                @endif
            @endif
        @endforeach
    </div>
</div>

@unless($readOnly)
    

    <!-- Modal Form Input Area (Warnai Ruangan) -->
    <div class="modal fade" id="areaModal-{{ $floorPlan->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--primary-green); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-draw-polygon me-2"></i>Tambah Area Ruangan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="areaForm-{{ $floorPlan->id }}">
                        <div class="mb-3">
                            <label for="areaRoom-{{ $floorPlan->id }}" class="form-label">
                                <i class="fas fa-door-open me-1"></i>Ruangan <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="areaRoom-{{ $floorPlan->id }}" name="room_id" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($floorPlan->rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <strong>Penting:</strong> Pilih ruangan untuk menampilkan simbol pengukuran (❌ ⭕ 🔺). 
                                Pastikan jumlah titik pengukuran sudah diisi di form ruangan.
                            </small>
                        </div>

                        <hr class="my-3">
                        <h6 class="mb-3">
                            <i class="fas fa-flask me-2"></i>Hasil Pengukuran NAB
                        </h6>
                        <p class="text-muted small mb-3">Masukkan hasil pengukuran. Sistem akan otomatis menentukan status NAB (merah/hijau) berdasarkan nilai yang diinput.</p>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="lightingValue-{{ $floorPlan->id }}" class="form-label">
                                    <span style="font-size: 1.2em;">❌</span> Pencahayaan
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="lightingValue-{{ $floorPlan->id }}" 
                                       name="lighting_value" 
                                       placeholder="Contoh: 290"
                                       data-parameter="pencahayaan">
                                <small class="text-muted">Lux Meter. NAB: ≥ 300 (hijau), &lt; 300 (merah)</small>
                                <div class="nab-status mt-1" id="pencahayaanStatus-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="dustValue-{{ $floorPlan->id }}" class="form-label">
                                    <span style="font-size: 1.2em;">⭕</span> Debu Total
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="dustValue-{{ $floorPlan->id }}" 
                                       name="dust_value" 
                                       placeholder="Contoh: 8"
                                       data-parameter="debu_total">
                                <small class="text-muted">mg/m³. NAB: &lt; 10 (hijau), ≥ 10 (merah)</small>
                                <div class="nab-status mt-1" id="debu_totalStatus-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>
                        </div>

                        <hr class="my-3">
                        <h6 class="mb-3">
                            <i class="fas fa-wind me-2"></i>Kualitas Udara Dalam Ruangan
                        </h6>
                        <p class="text-muted small mb-3">Masukkan hasil pengukuran untuk parameter kualitas udara dalam ruangan.</p>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="kudrSuhu-{{ $floorPlan->id }}" class="form-label">
                                    🔺 Suhu Ruangan
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="kudrSuhu-{{ $floorPlan->id }}" 
                                       name="kudr_suhu" 
                                       placeholder="Contoh: 25"
                                       data-parameter="kudr_suhu">
                                <small class="text-muted">°C. NAB: 23-26 (hijau), di luar range (merah)</small>
                                <div class="nab-status mt-1" id="kudr_suhuStatus-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="kudrRh-{{ $floorPlan->id }}" class="form-label">
                                    RH (Kelembaban)
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="kudrRh-{{ $floorPlan->id }}" 
                                       name="kudr_rh" 
                                       placeholder="Contoh: 50"
                                       data-parameter="kudr_rh">
                                <small class="text-muted">%. NAB: 40-60 (hijau), di luar range (merah)</small>
                                <div class="nab-status mt-1" id="kudr_rhStatus-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="kudrPergerakanUdara-{{ $floorPlan->id }}" class="form-label">
                                    Pergerakan Udara
                                </label>
                                <input type="number" 
                                       step="0.001"
                                       class="form-control measurement-input" 
                                       id="kudrPergerakanUdara-{{ $floorPlan->id }}" 
                                       name="kudr_pergerakan_udara" 
                                       placeholder="Contoh: 0.02"
                                       data-parameter="kudr_pergerakan_udara">
                                <small class="text-muted">m/dt. NAB: &lt; 0,03 (hijau), ≥ 0,03 (merah)</small>
                                <div class="nab-status mt-1" id="kudr_pergerakan_udaraStatus-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="kudrHcoh-{{ $floorPlan->id }}" class="form-label">
                                    HCOH
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="kudrHcoh-{{ $floorPlan->id }}" 
                                       name="kudr_hcoh" 
                                       placeholder="Contoh: 80"
                                       data-parameter="kudr_hcoh">
                                <small class="text-muted">µg/m³. NAB: &lt; 100 (hijau), ≥ 100 (merah)</small>
                                <div class="nab-status mt-1" id="kudr_hcohStatus-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="kudrCo2-{{ $floorPlan->id }}" class="form-label">
                                    CO²
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="kudrCo2-{{ $floorPlan->id }}" 
                                       name="kudr_co2" 
                                       placeholder="Contoh: 800"
                                       data-parameter="kudr_co2">
                                <small class="text-muted">Bds. NAB: &lt; 1000 (hijau), ≥ 1000 (merah)</small>
                                <div class="nab-status mt-1" id="kudr_co2Status-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="kudrCo-{{ $floorPlan->id }}" class="form-label">
                                    CO
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="kudrCo-{{ $floorPlan->id }}" 
                                       name="kudr_co" 
                                       placeholder="Contoh: 5"
                                       data-parameter="kudr_co">
                                <small class="text-muted">Bds. NAB: &lt; 8,7 (hijau), ≥ 8,7 (merah)</small>
                                <div class="nab-status mt-1" id="kudr_coStatus-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="kudrNo2-{{ $floorPlan->id }}" class="form-label">
                                    NO²
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="kudrNo2-{{ $floorPlan->id }}" 
                                       name="kudr_no2" 
                                       placeholder="Contoh: 100"
                                       data-parameter="kudr_no2">
                                <small class="text-muted">µg/m³. NAB: &lt; 150 (hijau), ≥ 150 (merah)</small>
                                <div class="nab-status mt-1" id="kudr_no2Status-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="kudrOksidan-{{ $floorPlan->id }}" class="form-label">
                                    Oksidan
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="kudrOksidan-{{ $floorPlan->id }}" 
                                       name="kudr_oksidan" 
                                       placeholder="Contoh: 90"
                                       data-parameter="kudr_oksidan">
                                <small class="text-muted">µg/m³. NAB: &lt; 120 (hijau), ≥ 120 (merah)</small>
                                <div class="nab-status mt-1" id="kudr_oksidanStatus-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="kudrOksigen-{{ $floorPlan->id }}" class="form-label">
                                    Oksigen
                                </label>
                                <input type="number" 
                                       step="0.01"
                                       class="form-control measurement-input" 
                                       id="kudrOksigen-{{ $floorPlan->id }}" 
                                       name="kudr_oksigen" 
                                       placeholder="Contoh: 21"
                                       data-parameter="kudr_oksigen">
                                <small class="text-muted">%. NAB: 19,5-23,5 (hijau), di luar range (merah)</small>
                                <div class="nab-status mt-1" id="kudr_oksigenStatus-{{ $floorPlan->id }}" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-traffic-light me-1"></i>Status NAB Area
                            </label>
                            <div id="areaCategoryDisplay-{{ $floorPlan->id }}" class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>Masukkan hasil pengukuran untuk melihat status NAB
                            </div>
                            <input type="hidden" id="areaCategory-{{ $floorPlan->id }}" name="category" value="dibawah_nab">
                        </div>

                        <div class="mb-3">
                            <label for="areaNotes-{{ $floorPlan->id }}" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Keterangan (opsional)
                            </label>
                            <textarea class="form-control" 
                                      id="areaNotes-{{ $floorPlan->id }}" 
                                      name="notes" 
                                      rows="2"
                                      placeholder="Keterangan tambahan"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="saveAreaBtn-{{ $floorPlan->id }}">
                        <i class="fas fa-save me-1"></i> Simpan Area
                    </button>
                </div>
            </div>
        </div>
    </div>
@endunless

@push('scripts')
@if(in_array(strtolower($floorPlan->file_type), ['png','jpg','jpeg']))
<script>
    (function () {
        const wrapperId = 'floorplan-wrapper-{{ $floorPlan->id }}';
        const imageId = 'floorplan-image-{{ $floorPlan->id }}';
        const overlayId = 'floorplan-overlay-{{ $floorPlan->id }}';
        const readOnly = @json($readOnly ?? false);

        const wrapper = document.getElementById(wrapperId);
        const image = document.getElementById(imageId);
        const overlay = document.getElementById(overlayId);

        if (!wrapper || !image || !overlay) return;

        // Mode area (warnai ruangan)
        let areaPoints = [];

        if (!readOnly) {
            // Event listener untuk double-click area yang sudah ada (untuk delete)
            overlay.addEventListener('dblclick', function(e) {
                // Hanya handle double-click pada polygon area
                if (e.target.tagName === 'polygon' && e.target.classList.contains('floorplan-area')) {
                    e.stopPropagation(); // Stop event bubbling
                    e.preventDefault();
                    const pointId = e.target.getAttribute('data-id');
                    if (pointId && confirm('Apakah Anda yakin ingin menghapus area ini?')) {
                        // Hapus area via AJAX
                        const deleteUrl = '{{ url("points") }}/' + pointId;
                        fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => {
                            if (!res.ok) {
                                return res.json().then(err => {
                                    throw new Error(err.message || 'HTTP error! status: ' + res.status);
                                });
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert('Area berhasil dihapus!');
                                location.reload();
                            } else {
                                alert('Gagal menghapus area: ' + (data.message || 'Terjadi kesalahan'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat menghapus area: ' + error.message);
                        });
                    }
                    return;
                }
            });

            wrapper.addEventListener('click', function (e) {
                // Jangan tambah titik jika klik pada area yang sudah ada atau point
                if (e.target.classList.contains('floorplan-point') || (e.target.tagName === 'polygon' && e.target.classList.contains('floorplan-area'))) {
                    return;
                }

                const rect = image.getBoundingClientRect();
                const xPercent = ((e.clientX - rect.left) / rect.width) * 100;
                const yPercent = ((e.clientY - rect.top) / rect.height) * 100;

                // Mode Area - tambah titik ke polygon
                areaPoints.push({ x: parseFloat(xPercent.toFixed(2)), y: parseFloat(yPercent.toFixed(2)) });
                
                // Tampilkan preview titik sementara
                const tempPoint = document.createElement('div');
                tempPoint.style.position = 'absolute';
                tempPoint.style.left = xPercent + '%';
                tempPoint.style.top = yPercent + '%';
                tempPoint.style.width = '8px';
                tempPoint.style.height = '8px';
                tempPoint.style.borderRadius = '50%';
                tempPoint.style.backgroundColor = '#007bff';
                tempPoint.style.border = '2px solid white';
                tempPoint.style.transform = 'translate(-50%, -50%)';
                tempPoint.style.pointerEvents = 'none';
                tempPoint.className = 'temp-area-point';
                overlay.appendChild(tempPoint);
            });

            // Double click atau right click untuk selesai gambar area (hanya jika sedang membuat area baru)
            wrapper.addEventListener('dblclick', function(e) {
                // Jangan save jika double-click pada area yang sudah ada
                if (e.target.tagName === 'polygon' && e.target.classList.contains('floorplan-area')) {
                    return;
                }
                if (areaPoints.length >= 3) {
                    e.preventDefault();
                    saveArea();
                }
            });

            wrapper.addEventListener('contextmenu', function(e) {
                if (areaPoints.length >= 3) {
                    e.preventDefault();
                    saveArea();
                }
            });

            // Simpan area: dipanggil saat double-click / klik kanan SETELAH kategori & catatan diisi lewat modal
            function saveArea() {
                if (areaPoints.length < 3) {
                    alert('Minimal 3 titik untuk membuat area.');
                    return;
                }

                // Buka modal Bootstrap untuk pilih kategori & catatan
                const modalEl = document.getElementById('areaModal-{{ $floorPlan->id }}');
                const formEl = document.getElementById('areaForm-{{ $floorPlan->id }}');
                const modal = new bootstrap.Modal(modalEl);

                // Reset form setiap kali
                formEl.reset();
                // Reset status display
                document.getElementById('areaCategoryDisplay-{{ $floorPlan->id }}').innerHTML = '<i class="fas fa-info-circle me-2"></i>Masukkan hasil pengukuran untuk melihat status NAB';
                document.getElementById('areaCategoryDisplay-{{ $floorPlan->id }}').className = 'alert alert-info mb-0';
                document.querySelectorAll('.nab-status').forEach(el => {
                    el.style.display = 'none';
                    el.innerHTML = '';
                });

                // Evaluasi NAB otomatis saat input nilai
                const measurementInputs = formEl.querySelectorAll('.measurement-input');
                const categoryDisplay = document.getElementById('areaCategoryDisplay-{{ $floorPlan->id }}');
                const categoryInput = document.getElementById('areaCategory-{{ $floorPlan->id }}');
                
                function evaluateNAB() {
                    let worstCategory = 'dibawah_nab'; // Default hijau
                    let hasMeasurement = false;
                    const nabRules = {
                        'pencahayaan': { threshold: 300, unit: 'Lux Meter', above: '<', below: '≥', type: 'threshold' },
                        'debu_total': { threshold: 10, unit: 'mg/m³', above: '>', below: '≤', type: 'threshold' },
                        'kudr_suhu': { min: 23, max: 26, unit: '°C', type: 'range' },
                        'kudr_rh': { min: 40, max: 60, unit: '%', type: 'range' },
                        'kudr_pergerakan_udara': { threshold: 0.03, unit: 'm/dt', above: '>', below: '≤', type: 'threshold' },
                        'kudr_hcoh': { threshold: 100, unit: 'µg/m³', above: '>', below: '≤', type: 'threshold' },
                        'kudr_co2': { threshold: 1000, unit: 'Bds', above: '>', below: '≤', type: 'threshold' },
                        'kudr_co': { threshold: 8.7, unit: 'Bds', above: '>', below: '≤', type: 'threshold' },
                        'kudr_no2': { threshold: 150, unit: 'µg/m³', above: '>', below: '≤', type: 'threshold' },
                        'kudr_oksidan': { threshold: 120, unit: 'µg/m³', above: '>', below: '≤', type: 'threshold' },
                        'kudr_oksigen': { min: 19.5, max: 23.5, unit: '%', type: 'range' }
                    };

                    measurementInputs.forEach(input => {
                        const value = parseFloat(input.value);
                        const parameter = input.getAttribute('data-parameter');
                        const statusEl = document.getElementById(parameter + 'Status-{{ $floorPlan->id }}');
                        
                        if (!isNaN(value) && value >= 0) {
                            hasMeasurement = true;
                            let meetsNAB = true;
                            let statusText = '';
                            const rule = nabRules[parameter];
                            
                            if (rule) {
                                if (rule.type === 'range') {
                                    meetsNAB = value >= rule.min && value <= rule.max;
                                    statusText = meetsNAB 
                                        ? `✓ Memenuhi NAB (${value} ${rule.unit} dalam range ${rule.min}-${rule.max} ${rule.unit})`
                                        : `✗ Tidak Memenuhi NAB (${value} ${rule.unit} di luar range ${rule.min}-${rule.max} ${rule.unit})`;
                                } else if (rule.type === 'threshold') {
                                    // Untuk pencahayaan, logika terbalik: >= 300 memenuhi, < 300 tidak memenuhi
                                    if (parameter === 'pencahayaan') {
                                        meetsNAB = value >= rule.threshold;
                                    } else {
                                        meetsNAB = value <= rule.threshold;
                                    }
                                    statusText = meetsNAB
                                        ? `✓ Memenuhi NAB (${value} ${rule.unit} ${rule.below} ${rule.threshold} ${rule.unit})`
                                        : `✗ Tidak Memenuhi NAB (${value} ${rule.unit} ${rule.above} ${rule.threshold} ${rule.unit})`;
                                }
                            }
                            
                            if (!meetsNAB) {
                                worstCategory = 'diatas_nab';
                            }
                            
                            if (statusEl) {
                                statusEl.style.display = 'block';
                                statusEl.className = 'nab-status mt-1';
                                statusEl.innerHTML = `<span class="badge bg-${meetsNAB ? 'success' : 'danger'}">${statusText}</span>`;
                            }
                        } else if (statusEl) {
                            statusEl.style.display = 'none';
                            statusEl.innerHTML = '';
                        }
                    });

                    // Update status area
                    if (hasMeasurement) {
                        categoryInput.value = worstCategory;
                        if (worstCategory === 'diatas_nab') {
                            categoryDisplay.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>Status: Di atas NAB (Merah)</strong> - Area akan ditampilkan dengan warna merah';
                            categoryDisplay.className = 'alert alert-danger mb-0';
                        } else {
                            categoryDisplay.innerHTML = '<i class="fas fa-check-circle me-2"></i><strong>Status: Di bawah NAB (Hijau)</strong> - Area akan ditampilkan dengan warna hijau';
                            categoryDisplay.className = 'alert alert-success mb-0';
                        }
                    } else {
                        categoryDisplay.innerHTML = '<i class="fas fa-info-circle me-2"></i>Masukkan hasil pengukuran untuk melihat status NAB';
                        categoryDisplay.className = 'alert alert-info mb-0';
                        categoryInput.value = 'dibawah_nab';
                    }
                }

                measurementInputs.forEach(input => {
                    input.addEventListener('input', evaluateNAB);
                });

                // Simpan handler sementara untuk tombol simpan area
                const saveBtn = document.getElementById('saveAreaBtn-{{ $floorPlan->id }}');
                const onSave = function () {
                    const category = categoryInput.value;
                    const notes = formEl.querySelector('[name="notes"]').value;
                    const roomId = formEl.querySelector('[name="room_id"]').value;
                    const lightingValue = formEl.querySelector('[name="lighting_value"]')?.value;
                    const dustValue = formEl.querySelector('[name="dust_value"]')?.value;
                    const kudrSuhu = formEl.querySelector('[name="kudr_suhu"]')?.value;
                    const kudrRh = formEl.querySelector('[name="kudr_rh"]')?.value;
                    const kudrPergerakanUdara = formEl.querySelector('[name="kudr_pergerakan_udara"]')?.value;
                    const kudrHcoh = formEl.querySelector('[name="kudr_hcoh"]')?.value;
                    const kudrCo2 = formEl.querySelector('[name="kudr_co2"]')?.value;
                    const kudrCo = formEl.querySelector('[name="kudr_co"]')?.value;
                    const kudrNo2 = formEl.querySelector('[name="kudr_no2"]')?.value;
                    const kudrOksidan = formEl.querySelector('[name="kudr_oksidan"]')?.value;
                    const kudrOksigen = formEl.querySelector('[name="kudr_oksigen"]')?.value;

                    if (!roomId) {
                        alert('Ruangan wajib dipilih untuk menampilkan simbol pengukuran.');
                        return;
                    }

                    // Normalisasi bentuk jika hanya 4 titik (buat persegi panjang rapi)
                    let normalizedPoints = areaPoints;
                    if (areaPoints.length === 4) {
                        const xs = areaPoints.map(p => p.x);
                        const ys = areaPoints.map(p => p.y);
                        const minX = Math.min(...xs);
                        const maxX = Math.max(...xs);
                        const minY = Math.min(...ys);
                        const maxY = Math.max(...ys);
                        normalizedPoints = [
                            { x: minX, y: minY }, // kiri atas
                            { x: maxX, y: minY }, // kanan atas
                            { x: maxX, y: maxY }, // kanan bawah
                            { x: minX, y: maxY }, // kiri bawah
                        ];
                    }

                    // Hitung center point untuk x, y dari titik yang sudah dinormalisasi
                    const centerX = normalizedPoints.reduce((sum, p) => sum + p.x, 0) / normalizedPoints.length;
                    const centerY = normalizedPoints.reduce((sum, p) => sum + p.y, 0) / normalizedPoints.length;

                    fetch("{{ route('points.store', $floorPlan) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            type: 'area',
                            x: parseFloat(centerX.toFixed(2)),
                            y: parseFloat(centerY.toFixed(2)),
                            category: category,
                            notes: notes,
                            coordinates: normalizedPoints,
                            room_id: roomId ? parseInt(roomId) : null,
                            lighting_value: lightingValue ? parseFloat(lightingValue) : null,
                            lighting_parameter: lightingValue ? 'pencahayaan' : null,
                            dust_value: dustValue ? parseFloat(dustValue) : null,
                            dust_parameter: dustValue ? 'debu_total' : null,
                            kudr_suhu: kudrSuhu ? parseFloat(kudrSuhu) : null,
                            kudr_rh: kudrRh ? parseFloat(kudrRh) : null,
                            kudr_pergerakan_udara: kudrPergerakanUdara ? parseFloat(kudrPergerakanUdara) : null,
                            kudr_hcoh: kudrHcoh ? parseFloat(kudrHcoh) : null,
                            kudr_co2: kudrCo2 ? parseFloat(kudrCo2) : null,
                            kudr_co: kudrCo ? parseFloat(kudrCo) : null,
                            kudr_no2: kudrNo2 ? parseFloat(kudrNo2) : null,
                            kudr_oksidan: kudrOksidan ? parseFloat(kudrOksidan) : null,
                            kudr_oksigen: kudrOksigen ? parseFloat(kudrOksigen) : null
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Hapus temp points
                            document.querySelectorAll('.temp-area-point').forEach(el => el.remove());

                            modal.hide();
                            
                            // Cek apakah ruangan dipilih
                            if (roomId) {
                                alert('Area berhasil disimpan! Simbol pengukuran akan muncul jika jumlah titik pengukuran sudah diisi di form ruangan.');
                            } else {
                                alert('Area berhasil disimpan. Peringatan: Ruangan tidak dipilih, simbol pengukuran tidak akan muncul.');
                            }
                            
                            location.reload(); // Reload untuk sync dengan database
                        } else {
                            alert('Gagal menyimpan area: ' + (data.message || 'Terjadi kesalahan'));
                        }
                    })
                    .catch(() => {
                        alert('Terjadi kesalahan saat menyimpan area.');
                    })
                    .finally(() => {
                        saveBtn.removeEventListener('click', onSave);
                    });
                };

                saveBtn.addEventListener('click', onSave);
                modal.show();
            }
        }

        // Update SVG polygon points saat gambar resize
        image.addEventListener('load', function() {
            const svg = overlay.querySelector('svg');
            if (svg) {
                const polygons = svg.querySelectorAll('polygon');
                polygons.forEach(polygon => {
                    // Points sudah dalam persen, tidak perlu update
                });
            }
        });

    })();
</script>
@endif
@endpush



