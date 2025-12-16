<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FloorPlan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FloorPlanController extends Controller
{
    public function index()
    {
        $floorPlans = FloorPlan::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);
        return view('floor-plans.index', compact('floorPlans'));
    }

    public function create()
    {
        return view('floor-plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('floor-plans', $fileName, 'public');

        $floorPlan = FloorPlan::create([
            'name' => $request->name,
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'description' => $request->description,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('floor-plans.edit', $floorPlan)
            ->with('success', 'Denah berhasil diupload! Sekarang Anda bisa menambahkan titik pengukuran.');
    }

    public function show(FloorPlan $floorPlan)
    {
        $this->authorize('view', $floorPlan);
        $floorPlan->load(['points', 'rooms']);
        return view('floor-plans.show', compact('floorPlan'));
    }

    public function edit(FloorPlan $floorPlan)
    {
        $this->authorize('update', $floorPlan);
        $floorPlan->load(['points' => function($query) {
            $query->orderBy('created_at', 'desc');
        }, 'rooms' => function($query) {
            $query->orderBy('name', 'asc');
        }]);
        return view('floor-plans.edit', compact('floorPlan'));
    }

    public function update(Request $request, FloorPlan $floorPlan)
    {
        $this->authorize('update', $floorPlan);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $floorPlan->update($request->only(['name', 'description']));

        return redirect()->route('floor-plans.index')
            ->with('success', 'Denah berhasil diupdate!');
    }

    public function destroy(FloorPlan $floorPlan)
    {
        $this->authorize('delete', $floorPlan);

        if (Storage::disk('public')->exists($floorPlan->file_path)) {
            Storage::disk('public')->delete($floorPlan->file_path);
        }

        $floorPlan->delete();

        return redirect()->route('floor-plans.index')
            ->with('success', 'Denah berhasil dihapus!');
    }

    public function print(FloorPlan $floorPlan)
    {
        $this->authorize('view', $floorPlan);
        $floorPlan->load(['points', 'rooms']);
        return view('floor-plans.print', compact('floorPlan'));
    }
}
