<?php

namespace Database\Seeders;

use App\Models\FloorPlan;
use App\Models\Point;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FloorPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('email', 'admin@pemetaan.com')->first();
        
        if (!$admin) {
            $this->command->warn('Admin user not found. Please run DatabaseSeeder first.');
            return;
        }

        // Check if denah.jpg exists in public/img/
        $sourceImage = public_path('img/denah.jpg');
        
        if (!file_exists($sourceImage)) {
            $this->command->error('File img/denah.jpg tidak ditemukan di folder public/img/');
            return;
        }

        // Copy image to storage
        $fileName = Str::random(40) . '.jpg';
        $filePath = 'floor-plans/' . $fileName;
        
        // Ensure directory exists
        $storagePath = storage_path('app/public/floor-plans');
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        // Copy file
        copy($sourceImage, storage_path('app/public/' . $filePath));

        // Create floor plan
        $floorPlan = FloorPlan::create([
            'name' => 'Denah Lantai 3',
            'file_path' => $filePath,
            'file_type' => 'jpg',
            'description' => 'Denah lantai 3 dengan berbagai ruangan laboratorium dan area kerja.',
            'user_id' => $admin->id,
        ]);

        $this->command->info("Created floor plan: {$floorPlan->name}");

        // Cleanup: Hapus SEMUA point measurements yang ada untuk floor plan ini
        // Karena kita akan membuat area baru, tidak perlu point measurements terpisah
        // Point measurements hanya untuk type 'point', bukan untuk area
        $this->command->info("Cleaning up all duplicate point measurements for this floor plan...");
        
        // Hapus semua point measurements yang memiliki room_id (karena area sudah punya room_id)
        $deletedByRoom = Point::where('floor_plan_id', $floorPlan->id)
            ->where('type', 'point')
            ->whereNotNull('room_id')
            ->delete();
        
        // Hapus semua point measurements yang koordinatnya sama dengan area manapun
        $tolerance = 0.1;
        $existingAreas = Point::where('floor_plan_id', $floorPlan->id)
            ->where('type', 'area')
            ->get();
        
        $deletedByCoord = 0;
        foreach ($existingAreas as $area) {
            $deleted = Point::where('floor_plan_id', $floorPlan->id)
                ->where('type', 'point')
                ->where(function($query) use ($area, $tolerance) {
                    $query->whereBetween('x', [$area->x - $tolerance, $area->x + $tolerance])
                          ->whereBetween('y', [$area->y - $tolerance, $area->y + $tolerance]);
                })
                ->delete();
            $deletedByCoord += $deleted;
        }
        
        $totalDeleted = $deletedByRoom + $deletedByCoord;
        if ($totalDeleted > 0) {
            $this->command->info("Deleted {$totalDeleted} duplicate point measurements ({$deletedByRoom} by room_id, {$deletedByCoord} by coordinates)");
        }

        // Create sample rooms with measurement points and areas
        $rooms = [
            [
                'name' => 'R. PENGOLAHAN DATA',
                'description' => 'Ruangan pengolahan data',
                'lighting_points' => 1, // Hanya 1 simbol ❌
                'dust_points' => 1,
                'air_quality_points' => 1,
                'area' => [
                    'coordinates' => [
                        ['x' => 60, 'y' => 30],
                        ['x' => 80, 'y' => 30],
                        ['x' => 80, 'y' => 50],
                        ['x' => 60, 'y' => 50],
                    ],
                    'category' => 'dibawah_nab',
                ],
            ],
            [
                'name' => 'R. UJIAN',
                'description' => 'Ruangan ujian',
                'lighting_points' => 1,
                'dust_points' => 0,
                'air_quality_points' => 1,
                'area' => [
                    'coordinates' => [
                        ['x' => 20, 'y' => 20],
                        ['x' => 40, 'y' => 20],
                        ['x' => 40, 'y' => 40],
                        ['x' => 20, 'y' => 40],
                    ],
                    'category' => 'diatas_nab',
                ],
            ],
            [
                'name' => 'R. TIMBANG',
                'description' => 'Ruangan timbang',
                'lighting_points' => 0,
                'dust_points' => 0,
                'air_quality_points' => 2,
                'area' => [
                    'coordinates' => [
                        ['x' => 15, 'y' => 50],
                        ['x' => 35, 'y' => 50],
                        ['x' => 35, 'y' => 70],
                        ['x' => 15, 'y' => 70],
                    ],
                    'category' => 'dibawah_nab',
                ],
            ],
            [
                'name' => 'MEJA BETON',
                'description' => 'Area meja beton',
                'lighting_points' => 1, // Hanya 1 simbol ❌
                'dust_points' => 0,
                'air_quality_points' => 1,
                'area' => [
                    'coordinates' => [
                        ['x' => 25, 'y' => 55],
                        ['x' => 55, 'y' => 55],
                        ['x' => 55, 'y' => 75],
                        ['x' => 25, 'y' => 75],
                    ],
                    'category' => 'diatas_nab',
                ],
            ],
        ];

        foreach ($rooms as $roomData) {
            $room = Room::create([
                'floor_plan_id' => $floorPlan->id,
                'name' => $roomData['name'],
                'description' => $roomData['description'],
                'lighting_points' => $roomData['lighting_points'] ?? 0,
                'dust_points' => $roomData['dust_points'] ?? 0,
                'air_quality_points' => $roomData['air_quality_points'] ?? 0,
            ]);

            // Create area for this room
            if (isset($roomData['area'])) {
                $areaData = $roomData['area'];
                $coords = $areaData['coordinates'];
                
                // Calculate center point
                $centerX = collect($coords)->avg('x');
                $centerY = collect($coords)->avg('y');
                
                // Hapus area yang sudah ada untuk room ini (jika ada) - untuk mencegah duplikasi
                Point::where('floor_plan_id', $floorPlan->id)
                    ->where('room_id', $room->id)
                    ->where('type', 'area')
                    ->delete();
                
                // Hapus point measurements yang duplikat dengan area ini (jika ada)
                $tolerance = 0.1;
                Point::where('floor_plan_id', $floorPlan->id)
                    ->where('type', 'point')
                    ->where(function($query) use ($centerX, $centerY, $tolerance) {
                        $query->whereBetween('x', [$centerX - $tolerance, $centerX + $tolerance])
                              ->whereBetween('y', [$centerY - $tolerance, $centerY + $tolerance]);
                    })
                    ->delete();
                
                // Hapus point measurements yang memiliki room_id sama
                Point::where('floor_plan_id', $floorPlan->id)
                    ->where('type', 'point')
                    ->where('room_id', $room->id)
                    ->delete();
                
                // Buat area baru (hanya 1 area per room)
                Point::create([
                    'floor_plan_id' => $floorPlan->id,
                    'room_id' => $room->id,
                    'type' => 'area',
                    'x' => $centerX,
                    'y' => $centerY,
                    'category' => $areaData['category'],
                    'notes' => $room->name,
                    'coordinates' => $coords,
                ]);
            }

            $this->command->info("Created room: {$room->name} with measurement points and area");
        }

        $this->command->info('Floor plan seeder completed successfully!');
        $this->command->info('Area sudah dibuat dan dikaitkan dengan ruangan. Simbol pengukuran akan muncul langsung.');
    }
}

