<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Denah - {{ $floorPlan->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 25px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #198754;
        }
        .header h1 {
            font-size: 24px;
            color: #198754;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .header h2 {
            font-size: 18px;
            color: #333;
            margin: 0;
            font-weight: 400;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #198754;
        }
        .info-table {
            width: 100%;
            margin-bottom: 0;
            font-size: 14px;
        }
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 150px;
            font-weight: 600;
            color: #555;
        }
        .legend {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .legend-title {
            font-weight: 600;
            margin-bottom: 12px;
            color: #333;
            font-size: 14px;
        }
        .legend-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            font-size: 13px;
            color: #555;
        }
        .legend-color {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            margin-right: 8px;
            border: 2px solid #fff;
            box-shadow: 0 0 2px rgba(0,0,0,0.2);
        }
        .legend-symbol {
            font-size: 18px;
            margin-right: 8px;
            display: inline-block;
        }
        .measurement-symbol {
            position: absolute;
            font-size: 6px;
            line-height: 1;
            transform: translate(-50%, -50%);
            z-index: 20;
            text-shadow: 0.5px 0.5px 1px rgba(255,255,255,1), -0.5px -0.5px 1px rgba(255,255,255,1), 0 0 2px rgba(255,255,255,1);
            font-weight: normal;
        }
        .measurement-symbol.air-quality {
            font-size: 5px;
        }
        .measurement-symbol-group {
            display: inline-block;
            margin: 0 2px;
        }
        .floorplan-wrapper {
            position: relative;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            margin-top: 10px;
            margin-bottom: 20px;
            page-break-inside: avoid;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        }
        .floorplan-point {
            position: absolute;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid #fff;
            box-shadow: 0 0 4px rgba(0,0,0,0.6);
            z-index: 10;
        }
        .floorplan-point.diatas_nab {
            background-color: #dc3545 !important;
        }
        .floorplan-point.dibawah_nab {
            background-color: #198754 !important;
        }
        .points-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .points-table th,
        .points-table td {
            border: 1px solid #dee2e6;
            padding: 10px 12px;
            text-align: left;
        }
        .points-table th {
            background-color: #198754;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        .points-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .points-table tbody tr:hover {
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                padding: 15mm;
            }
            .legend-row {
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header">
            <h1>Pemetaan Lingkungan Kerja</h1>
            <h2>{{ $floorPlan->name }}</h2>
        </div>

        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td>Nama Denah</td>
                    <td>: {{ $floorPlan->name }}</td>
                </tr>
                <tr>
                    <td>Keterangan</td>
                    <td>: {{ $floorPlan->description ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Jumlah Titik</td>
                    <td>: {{ $floorPlan->points->count() }} titik</td>
                </tr>
                <tr>
                    <td>Jumlah Ruangan</td>
                    <td>: {{ $floorPlan->rooms->count() }} ruangan</td>
                </tr>
            </table>
        </div>

        <div class="legend">
            <div class="legend-title">Keterangan Simbol</div>
            <div class="legend-row">
                <div class="legend-item">
                    <span class="legend-color" style="background-color:#dc3545"></span>
                    <span>Di atas NAB</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color:#198754"></span>
                    <span>Di bawah NAB</span>
                </div>
                <div class="legend-item">
                    <span class="legend-symbol">❌</span>
                    <span>Pengukuran Pencahayaan</span>
                </div>
                <div class="legend-item">
                    <span class="legend-symbol">⭕</span>
                    <span>Debu Total</span>
                </div>
                <div class="legend-item">
                    <span class="legend-symbol" style="font-size: 14px;">🔺</span>
                    <span>Kualitas Udara Dalam Ruangan</span>
                </div>
            </div>
        </div>

    @if(in_array(strtolower($floorPlan->file_type), ['png','jpg','jpeg']))
        <div class="floorplan-wrapper" style="position: relative; display: inline-block; width: 100%;">
            @php
                $imagePath = public_path('storage/'.$floorPlan->file_path);
                $imageUrl = asset('storage/'.$floorPlan->file_path);
            @endphp
            <img src="{{ $imageUrl }}" alt="{{ $floorPlan->name }}" class="floorplan-image" id="print-floorplan-image" style="width: 100%; height: auto; display: block;">
            @if($floorPlan->points->count() > 0)
                <div class="floorplan-overlay" id="print-floorplan-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                    <svg style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" viewBox="0 0 100 100" preserveAspectRatio="none">
                        @foreach($floorPlan->points as $point)
                            @if($point->type === 'area' && $point->coordinates && is_array($point->coordinates))
                                <polygon class="floorplan-area {{ $point->category }}"
                                         points="{{ collect($point->coordinates)->map(function($coord) {
                                             return ($coord['x'] ?? 0) . ',' . ($coord['y'] ?? 0);
                                         })->join(' ') }}"
                                         style="fill: {{ $point->category === 'diatas_nab' ? 'rgba(220, 53, 69, 0.08)' : 'rgba(25, 135, 84, 0.08)' }}; 
                                                stroke: {{ $point->category === 'diatas_nab' ? '#dc3545' : '#198754' }}; 
                                                stroke-width: 1;">
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
                                <div class="floorplan-point {{ $point->category }}"
                                     style="position: absolute; left: {{ $point->x }}%; top: {{ $point->y }}%; width: 16px; height: 16px; border-radius: 50%; transform: translate(-50%, -50%); border: 2px solid #fff; box-shadow: 0 0 4px rgba(0,0,0,0.6);"></div>
                            @endif
                        @endif
                    @endforeach
                    
                    {{-- Tampilkan simbol pengukuran untuk setiap ruangan --}}
                    @foreach($floorPlan->rooms as $room)
                        @php
                            // Cari area yang terkait dengan ruangan ini
                            $roomArea = $floorPlan->points->where('room_id', $room->id)->where('type', 'area')->first();
                            
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
            @endif
        </div>
    @else
        <p><strong>Catatan:</strong> File denah bertipe {{ strtoupper($floorPlan->file_type) }}. Untuk visual titik di atas denah, gunakan file gambar (PNG/JPG).</p>
    @endif

        @if($floorPlan->points->count() > 0)
            <table class="points-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Posisi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($floorPlan->points as $index => $point)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $point->type === 'area' ? 'Area' : 'Titik' }}</td>
                            <td>
                                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: {{ $point->category === 'diatas_nab' ? '#dc3545' : '#198754' }}; margin-right: 5px;"></span>
                                {{ $point->category === 'diatas_nab' ? 'Di atas NAB' : 'Di bawah NAB' }}
                            </td>
                            <td>
                                @if($point->type === 'area')
                                    Area ({{ count($point->coordinates ?? []) }} titik)
                                @else
                                    {{ $point->x }}%, {{ $point->y }}%
                                @endif
                            </td>
                            <td>{{ $point->notes ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer">
            <p>Dicetak pada: {{ date('d F Y, H:i') }} | Pemetaan Lingkungan Kerja</p>
        </div>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>


