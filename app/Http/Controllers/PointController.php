<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Point;
use App\Models\FloorPlan;
use App\Models\Room;
use App\Support\NabEvaluator;

class PointController extends Controller
{
    public function store(Request $request, FloorPlan $floorPlan)
    {
        $this->authorize('update', $floorPlan);

        $request->validate([
            'type' => 'required|in:point,area',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'parameter' => 'nullable|string',
            'value' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'category' => 'nullable|in:diatas_nab,dibawah_nab',
            'notes' => 'nullable|string',
            'coordinates' => 'nullable|array',
            'room_id' => 'nullable|exists:rooms,id',
            'lighting_value' => 'nullable|numeric|min:0',
            'lighting_parameter' => 'nullable|string',
            'dust_value' => 'nullable|numeric|min:0',
            'dust_parameter' => 'nullable|string',
            'kudr_suhu' => 'nullable|numeric',
            'kudr_rh' => 'nullable|numeric|min:0|max:100',
            'kudr_pergerakan_udara' => 'nullable|numeric|min:0',
            'kudr_hcoh' => 'nullable|numeric|min:0',
            'kudr_co2' => 'nullable|numeric|min:0',
            'kudr_co' => 'nullable|numeric|min:0',
            'kudr_no2' => 'nullable|numeric|min:0',
            'kudr_oksidan' => 'nullable|numeric|min:0',
            'kudr_oksigen' => 'nullable|numeric|min:0|max:100',
        ]);

        $category = $request->category;
        $worstCategory = 'dibawah_nab'; // Default hijau
        $hasMeasurement = false;

        // Evaluasi NAB untuk setiap parameter yang diinput
        $measurements = [];
        
        if ($request->filled('lighting_value') && $request->filled('lighting_parameter')) {
            $result = NabEvaluator::evaluate($request->lighting_parameter, (float) $request->lighting_value);
            $measurements[] = [
                'parameter' => $request->lighting_parameter,
                'value' => (float) $request->lighting_value,
                'unit' => $result['unit'],
                'category' => $result['category'],
                'meets_nab' => $result['meets_nab'],
            ];
            if ($result['category'] === 'diatas_nab') {
                $worstCategory = 'diatas_nab';
            }
            $hasMeasurement = true;
        }

        if ($request->filled('dust_value') && $request->filled('dust_parameter')) {
            $result = NabEvaluator::evaluate($request->dust_parameter, (float) $request->dust_value);
            $measurements[] = [
                'parameter' => $request->dust_parameter,
                'value' => (float) $request->dust_value,
                'unit' => $result['unit'],
                'category' => $result['category'],
                'meets_nab' => $result['meets_nab'],
            ];
            if ($result['category'] === 'diatas_nab') {
                $worstCategory = 'diatas_nab';
            }
            $hasMeasurement = true;
        }

        // Handle semua parameter kualitas udara dalam ruangan
        $kudrParameters = [
            'kudr_suhu' => $request->kudr_suhu,
            'kudr_rh' => $request->kudr_rh,
            'kudr_pergerakan_udara' => $request->kudr_pergerakan_udara,
            'kudr_hcoh' => $request->kudr_hcoh,
            'kudr_co2' => $request->kudr_co2,
            'kudr_co' => $request->kudr_co,
            'kudr_no2' => $request->kudr_no2,
            'kudr_oksidan' => $request->kudr_oksidan,
            'kudr_oksigen' => $request->kudr_oksigen,
        ];

        foreach ($kudrParameters as $parameter => $value) {
            if ($request->filled($parameter)) {
                $result = NabEvaluator::evaluate($parameter, (float) $value);
                $measurements[] = [
                    'parameter' => $parameter,
                    'value' => (float) $value,
                    'unit' => $result['unit'],
                    'category' => $result['category'],
                    'meets_nab' => $result['meets_nab'],
                ];
                if ($result['category'] === 'diatas_nab') {
                    $worstCategory = 'diatas_nab';
                }
                $hasMeasurement = true;
            }
        }

        // Jika ada hasil pengukuran, gunakan kategori terburuk untuk area
        if ($hasMeasurement && $request->type === 'area') {
            $category = $worstCategory;
        } elseif ($request->filled('parameter') && $request->filled('value')) {
            // Fallback untuk point measurement langsung
            $result = NabEvaluator::evaluate($request->parameter, (float) $request->value);
            $category = $result['category'];
        }

        // Fallback: jika kategori masih kosong, default ke dibawah_nab
        if (! $category) {
            $category = 'dibawah_nab';
        }

        // Jika type adalah area, hanya buat 1 area point (tidak perlu buat point measurements terpisah)
        // Measurements sudah disimpan di room untuk menampilkan simbol
        if ($request->type === 'area') {
            // Hapus SEMUA point measurements yang duplikat dengan area ini
            // Strategi: hapus point measurements yang memiliki room_id sama ATAU koordinat yang sama
            $tolerance = 0.1; // toleransi 0.1%
            
            // Hapus point measurements yang memiliki room_id sama dengan area yang akan dibuat
            if ($request->room_id) {
                Point::where('floor_plan_id', $floorPlan->id)
                    ->where('type', 'point')
                    ->where('room_id', $request->room_id)
                    ->delete();
            }
            
            // Hapus point measurements yang koordinatnya sama dengan area yang akan dibuat
            Point::where('floor_plan_id', $floorPlan->id)
                ->where('type', 'point')
                ->where(function($query) use ($request, $tolerance) {
                    $query->whereBetween('x', [$request->x - $tolerance, $request->x + $tolerance])
                          ->whereBetween('y', [$request->y - $tolerance, $request->y + $tolerance]);
                })
                ->delete();
            
            // Hapus point measurements yang koordinatnya sama dengan area yang sudah ada
            $existingAreas = Point::where('floor_plan_id', $floorPlan->id)
                ->where('type', 'area')
                ->get();
            
            foreach ($existingAreas as $area) {
                Point::where('floor_plan_id', $floorPlan->id)
                    ->where('type', 'point')
                    ->where(function($query) use ($area, $tolerance) {
                        $query->whereBetween('x', [$area->x - $tolerance, $area->x + $tolerance])
                              ->whereBetween('y', [$area->y - $tolerance, $area->y + $tolerance]);
                    })
                    ->delete();
            }
            
            // Simpan semua measurements sebagai JSON
            $measurementsData = [];
            foreach ($measurements as $measurement) {
                $measurementsData[] = [
                    'parameter' => $measurement['parameter'],
                    'value' => $measurement['value'],
                    'unit' => $measurement['unit'],
                ];
            }
            
            $areaPoint = Point::create([
                'floor_plan_id' => $floorPlan->id,
                'room_id' => $request->room_id,
                'type' => $request->type,
                'x' => $request->x,
                'y' => $request->y,
                'parameter' => $request->parameter,
                'value' => $request->value,
                'unit' => $request->unit,
                'category' => $category,
                'meets_nab' => null, // Area tidak punya meets_nab langsung
                'notes' => $request->notes,
                'coordinates' => $request->coordinates,
                'measurements' => !empty($measurementsData) ? $measurementsData : null,
            ]);
        } else {
            // Untuk type 'point', buat point measurement
            $point = Point::create([
                'floor_plan_id' => $floorPlan->id,
                'room_id' => $request->room_id,
                'type' => $request->type,
                'x' => $request->x,
                'y' => $request->y,
                'parameter' => $request->parameter,
                'value' => $request->value,
                'unit' => $request->unit,
                'category' => $category,
                'meets_nab' => $hasMeasurement && count($measurements) > 0 ? $measurements[0]['meets_nab'] : null,
                'notes' => $request->notes,
                'coordinates' => $request->coordinates,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Titik berhasil ditambahkan']);
    }

    public function update(Request $request, Point $point)
    {
        $this->authorize('update', $point->floorPlan);

        $request->validate([
            'parameter' => 'nullable|string',
            'value' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'category' => 'nullable|in:diatas_nab,dibawah_nab',
            'notes' => 'nullable|string',
        ]);

        $data = $request->only(['parameter', 'value', 'unit', 'category', 'notes']);

        // Hitung ulang NAB jika parameter & value diisi
        if ($request->filled('parameter') && $request->filled('value')) {
            $result = NabEvaluator::evaluate($request->parameter, (float) $request->value);
            $data['category'] = $result['category'];
            $data['meets_nab'] = $result['meets_nab'];
            $data['unit'] = $data['unit'] ?: $result['unit'];
        }

        $point->update($data);

        return response()->json(['success' => true, 'message' => 'Titik berhasil diupdate']);
    }

    public function destroy(Point $point)
    {
        try {
            $this->authorize('update', $point->floorPlan);

            $point->delete();

            return response()->json(['success' => true, 'message' => 'Titik berhasil dihapus']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus titik ini.'], 403);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getRooms(FloorPlan $floorPlan)
    {
        $this->authorize('view', $floorPlan);

        $rooms = Room::where('floor_plan_id', $floorPlan->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($rooms);
    }

    public function cleanupDuplicates(FloorPlan $floorPlan)
    {
        $this->authorize('update', $floorPlan);

        $deletedCount = 0;
        $tolerance = 0.1; // toleransi 0.1%

        // Ambil semua area points
        $areaPoints = Point::where('floor_plan_id', $floorPlan->id)
            ->where('type', 'area')
            ->get();

        // Untuk setiap area, hapus point measurements yang duplikat
        foreach ($areaPoints as $areaPoint) {
            $deleted = Point::where('floor_plan_id', $floorPlan->id)
                ->where('type', 'point')
                ->where(function($query) use ($areaPoint, $tolerance) {
                    $query->whereBetween('x', [$areaPoint->x - $tolerance, $areaPoint->x + $tolerance])
                          ->whereBetween('y', [$areaPoint->y - $tolerance, $areaPoint->y + $tolerance]);
                })
                ->delete();
            
            $deletedCount += $deleted;
        }

        return response()->json([
            'success' => true, 
            'message' => "Berhasil menghapus {$deletedCount} point measurements duplikat"
        ]);
    }
}
