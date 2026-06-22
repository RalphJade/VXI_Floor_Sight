<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'campaign',
        'subnet',
        'vlan_a',
        'vlan_b',
        'vlan_c',
    ];


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function workstations(): HasMany
    {
        return $this->hasMany(Workstation::class);
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
