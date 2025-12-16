<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\FloorPlan;

class FileController extends Controller
{
    public function show($id)
    {
        $floorPlan = FloorPlan::findOrFail($id);
        
        if (!Storage::disk('public')->exists($floorPlan->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($floorPlan->file_path);
    }
}
