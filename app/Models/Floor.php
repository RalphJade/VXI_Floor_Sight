<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'floor_number',
        'floor_name',
        'total_seats',
        'description',
        'svg_map_path',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all bays on this floor.
     */
    public function bays(): HasMany
    {
        return $this->hasMany(Bay::class);
    }

    /**
     * Get all workstations on this floor through bays.
     */
    public function workstations()
    {
        return $this->hasManyThrough(Workstation::class, Bay::class);
    }

    /**
     * Get count of active workstations on floor.
     */
    public function activeWorkstationCount(): int
    {
        return $this->workstations()->where('status', 'active')->count();
    }

    /**
     * Get count of offline workstations on floor.
     */
    public function offlineWorkstationCount(): int
    {
        return $this->workstations()->where('status', 'offline')->count();
    }

    /**
     * Get count of empty seats on floor.
     */
    public function emptyWorkstationCount(): int
    {
        return $this->workstations()->where('status', 'empty')->count();
    }
}
