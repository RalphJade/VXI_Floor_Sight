<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workstation extends Model
{
    use HasFactory;

    protected $fillable = [
        'bay_id',
        'station_id',
        'hostname',
        'ip_address',
        'mac_address',
        'type',
        'x',
        'y',
        'status',
        'voice_vlan',
        'data_vlan',
        'headset_serial',
        'agent_name',
        'asset_tag',
        'last_ping_at',
        'last_sync_at',
        'notes',
    ];

    protected $casts = [
        'last_ping_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the bay this workstation belongs to.
     */
    public function bay(): BelongsTo
    {
        return $this->belongsTo(Bay::class);
    }

    /**
     * Get the floor through the bay.
     */
    public function floor()
    {
        return $this->hasOneThrough(Floor::class, Bay::class, 'id', 'id', 'bay_id', 'floor_id');
    }

    /**
     * Get all audit logs for this workstation.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the SVG element ID for this workstation.
     */
    public function getSvgElementId(): string
    {
        return sprintf("seat-F%02d-%s%02d", $this->floor->floor_number, $this->bay->bay_letter, $this->station_id);
    }

    /**
     * Check if workstation is connected.
     */
    public function isConnected(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if workstation is offline.
     */
    public function isOffline(): bool
    {
        return $this->status === 'offline';
    }

    /**
     * Check if workstation is empty.
     */
    public function isEmpty(): bool
    {
        return $this->status === 'empty';
    }

    /**
     * Get the CSS status class for styling.
     */
    public function getStatusClass(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-600',
            'offline' => 'bg-red-600 animate-pulse',
            'empty' => 'bg-gray-600',
            default => 'bg-gray-500',
        };
    }

    /**
     * Scope query to get only active workstations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope query to get only offline workstations.
     */
    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    /**
     * Scope query to get only empty workstations.
     */
    public function scopeEmpty($query)
    {
        return $query->where('status', 'empty');
    }

    /**
     * Search workstations by hostname, ip, agent name, or asset tag.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('hostname', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%")
                ->orWhere('agent_name', 'like', "%{$term}%")
                ->orWhere('asset_tag', 'like', "%{$term}%")
                ->orWhere('mac_address', 'like', "%{$term}%");
        });
    }
}
