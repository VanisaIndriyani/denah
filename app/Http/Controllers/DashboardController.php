<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FloorPlan;
use App\Models\Room;

class DashboardController extends Controller
{
    public function index()
    {
        $floorPlans = FloorPlan::with(['user', 'points'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        $totalRooms = Room::whereHas('floorPlan', function($query) {
            $query->where('user_id', auth()->id());
        })->count();

        return view('dashboard', compact('floorPlans', 'totalRooms'));
    }
}
