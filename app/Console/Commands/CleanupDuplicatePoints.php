<?php

namespace App\Console\Commands;

use App\Models\FloorPlan;
use App\Models\Point;
use Illuminate\Console\Command;

class CleanupDuplicatePoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:cleanup-duplicates {--floor-plan-id= : ID floor plan spesifik (opsional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan point measurements yang duplikat dengan area points';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $floorPlanId = $this->option('floor-plan-id');
        $tolerance = 0.1; // toleransi 0.1%
        $totalDeleted = 0;

        if ($floorPlanId) {
            $floorPlans = FloorPlan::where('id', $floorPlanId)->get();
        } else {
            $floorPlans = FloorPlan::all();
        }

        if ($floorPlans->isEmpty()) {
            $this->error('Tidak ada floor plan yang ditemukan.');
            return 1;
        }

        $this->info("Memulai cleanup duplikat point measurements...");
        $this->newLine();

        foreach ($floorPlans as $floorPlan) {
            $this->info("Memproses: {$floorPlan->name} (ID: {$floorPlan->id})");
            
            // Ambil semua area points
            $areaPoints = Point::where('floor_plan_id', $floorPlan->id)
                ->where('type', 'area')
                ->get();

            $deletedCount = 0;

            // Untuk setiap area, hapus point measurements yang duplikat
            foreach ($areaPoints as $areaPoint) {
                // Hapus berdasarkan koordinat
                $deleted = Point::where('floor_plan_id', $floorPlan->id)
                    ->where('type', 'point')
                    ->where(function($query) use ($areaPoint, $tolerance) {
                        $query->whereBetween('x', [$areaPoint->x - $tolerance, $areaPoint->x + $tolerance])
                              ->whereBetween('y', [$areaPoint->y - $tolerance, $areaPoint->y + $tolerance]);
                    })
                    ->delete();
                
                $deletedCount += $deleted;

                // Hapus berdasarkan room_id (jika area punya room_id)
                if ($areaPoint->room_id) {
                    $deleted = Point::where('floor_plan_id', $floorPlan->id)
                        ->where('type', 'point')
                        ->where('room_id', $areaPoint->room_id)
                        ->delete();
                    
                    $deletedCount += $deleted;
                }
            }

            $totalDeleted += $deletedCount;
            $this->line("  ✓ Dihapus {$deletedCount} point measurements duplikat");
        }

        $this->newLine();
        $this->info("Selesai! Total {$totalDeleted} point measurements duplikat telah dihapus.");
        
        return 0;
    }
}
