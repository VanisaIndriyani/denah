<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Point extends Model
{
    protected $fillable = [
        'floor_plan_id',
        'room_id',
        'type',
        'x',
        'y',
        'category',
        'parameter',
        'value',
        'unit',
        'meets_nab',
        'notes',
        'coordinates',
        'measurements',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'measurements' => 'array',
        'x' => 'decimal:2',
        'y' => 'decimal:2',
        'value' => 'decimal:2',
        'meets_nab' => 'boolean',
    ];

    public function floorPlan(): BelongsTo
    {
        return $this->belongsTo(FloorPlan::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getColorAttribute(): string
    {
        return $this->category === 'diatas_nab' ? '#dc3545' : '#28a745'; // merah atau hijau
    }
}
