<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'floor_plan_id',
        'name',
        'description',
        'lighting_points',
        'dust_points',
        'air_quality_points',
    ];

    public function floorPlan(): BelongsTo
    {
        return $this->belongsTo(FloorPlan::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }
}

