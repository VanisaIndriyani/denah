<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\FloorPlan;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['floorPlan', 'points'])
            ->whereHas('floorPlan', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->paginate(15);

        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        $floorPlans = FloorPlan::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('rooms.create', compact('floorPlans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'floor_plan_id' => 'required|exists:floor_plans,id',
            'description' => 'nullable|string',
            'lighting_points' => 'nullable|integer|min:0',
            'dust_points' => 'nullable|integer|min:0',
            'air_quality_points' => 'nullable|integer|min:0',
        ]);

        $floorPlan = FloorPlan::findOrFail($request->floor_plan_id);
        $this->authorize('update', $floorPlan);

        Room::create([
            'name' => $request->name,
            'floor_plan_id' => $request->floor_plan_id,
            'description' => $request->description,
            'lighting_points' => $request->lighting_points ?? 0,
            'dust_points' => $request->dust_points ?? 0,
            'air_quality_points' => $request->air_quality_points ?? 0,
        ]);

        return redirect()->route('rooms.index')
            ->with('success', 'Ruangan berhasil ditambahkan!');
    }

    public function edit(Room $room)
    {
        $this->authorize('update', $room->floorPlan);

        $floorPlans = FloorPlan::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('rooms.edit', compact('room', 'floorPlans'));
    }

    public function update(Request $request, Room $room)
    {
        $this->authorize('update', $room->floorPlan);

        $request->validate([
            'name' => 'required|string|max:255',
            'floor_plan_id' => 'required|exists:floor_plans,id',
            'description' => 'nullable|string',
            'lighting_points' => 'nullable|integer|min:0',
            'dust_points' => 'nullable|integer|min:0',
            'air_quality_points' => 'nullable|integer|min:0',
        ]);

        $floorPlan = FloorPlan::findOrFail($request->floor_plan_id);
        $this->authorize('update', $floorPlan);

        $room->update($request->only([
            'name', 
            'floor_plan_id', 
            'description',
            'lighting_points',
            'dust_points',
            'air_quality_points'
        ]));

        return redirect()->route('rooms.index')
            ->with('success', 'Ruangan berhasil diupdate!');
    }

    public function destroy(Room $room)
    {
        $this->authorize('update', $room->floorPlan);

        $room->delete();

        return redirect()->route('rooms.index')
            ->with('success', 'Ruangan berhasil dihapus!');
    }
}

