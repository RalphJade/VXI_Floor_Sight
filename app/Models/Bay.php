<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bay extends Model
{
    use HasFactory;

    protected $fillable = [
        'floor_id',
        'bay_letter',
        'client_campaign_name',
        'seat_count',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the floor this bay belongs to.
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * Get all workstations in this bay.
     */
    public function workstations(): HasMany
    {
        return $this->hasMany(Workstation::class);
    }

    /**
     * Get count of active workstations in bay.
     */
    public function activeWorkstationCount(): int
    {
        return $this->workstations()->where('status', 'active')->count();
    }

    /**
     * Get count of offline workstations in bay.
     */
    public function offlineWorkstationCount(): int
    {
        return $this->workstations()->where('status', 'offline')->count();
    }

    /**
     * Get occupancy percentage for this bay.
     */
    public function occupancyPercentage(): float
    {
        $total = $this->workstations()->count();
        if ($total === 0) {
            return 0;
        }

        $active = $this->activeWorkstationCount();
        return ($active / $total) * 100;
    }
}
