<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workstation;

class WorkstationPolicy
{
    /**
     * Determine whether the user can view the workstation.
     */
    public function view(User $user, Workstation $workstation): bool
    {
        // IT Admin can view all workstations
        if ($user->isItAdmin()) {
            return true;
        }

        // Desktop Technician can view all workstations
        if ($user->isDesktopTechnician()) {
            return true;
        }

        // Operations Manager can view workstations in their assigned bay
        if ($user->isOperationsManager() && $user->assigned_bay_id) {
            return $workstation->bay_id === $user->assigned_bay_id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the workstation.
     */
    public function update(User $user, Workstation $workstation): bool
    {
        // IT Admin can update all workstations
        if ($user->isItAdmin()) {
            return true;
        }

        // Desktop Technician can update workstation metadata
        if ($user->isDesktopTechnician()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the workstation.
     */
    public function delete(User $user, Workstation $workstation): bool
    {
        // Only IT Admin can delete workstations
        return $user->isItAdmin();
    }

    /**
     * Determine whether the user can create a workstation.
     */
    public function create(User $user): bool
    {
        return $user->isItAdmin();
    }

    /**
     * Determine whether the user can trigger remote session.
     */
    public function remoteSession(User $user, Workstation $workstation): bool
    {
        // IT Admin can trigger remote sessions
        if ($user->isItAdmin()) {
            return true;
        }

        // Desktop Technician can trigger remote sessions
        if ($user->isDesktopTechnician()) {
            return true;
        }

        return false;
    }
}
