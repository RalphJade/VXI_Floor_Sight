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
        'floor_id',
        'name',
        'type',
        'hostname',
        'ip',
        'mac',
        'status',
        'agent',
        'x',
        'y',
        'model',        
        'ram',            
        'storage',        
        'serial_number',
    ];


    protected $casts = [
        'floor_id' => 'integer',
        'x' => 'integer',
        'y' => 'integer',
        'last_ping_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * Get the bay this workstation belongs to.
     */
    public function bay(): BelongsTo
    {
        return $this->belongsTo(Bay::class, 'bay_id');
    }



    /**
     * Get all audit logs for this workstation.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function getSvgElementId(): string
    {
        // Legacy helper (used by old Bay-based UI). With hard-aligned schema we no longer rely on Bay.
        return sprintf('seat-F%s-%s', $this->floor_id, $this->name);
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
                ->orWhere('ip', 'like', "%{$term}%")
                ->orWhere('agent', 'like', "%{$term}%")
                ->orWhere('mac', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }

}
