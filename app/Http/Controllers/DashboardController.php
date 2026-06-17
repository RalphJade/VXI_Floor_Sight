<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Bay;
use App\Models\Floor;
use App\Models\Workstation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the main dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Determine which floors the user can access
        if ($user->isItAdmin() || $user->isDesktopTechnician()) {
            // IT Admin and Desktop Technicians can see all floors
            $floors = Floor::with('bays.workstations')->orderBy('floor_number')->get();
        } else {
            // Operations Managers can only see their assigned bay's floor
            if (!$user->assigned_bay_id) {
                abort(403, 'No campaign assigned to your account.');
            }

            $bay = Bay::with('floor')->find($user->assigned_bay_id);
            $floors = Floor::where('id', $bay->floor_id)->get();
        }

        // Get the currently selected floor (default to first floor)
        $currentFloorId = $request->query('floor', $floors->first()?->id);
        $currentFloor = $floors->findOrFail($currentFloorId);

        // Get bays for the current floor
        $bays = $currentFloor->bays()->with('workstations')->get();

        // Calculate metrics for the current floor
        $metrics = $this->getFloorMetrics($currentFloor);

        // Get recent audit logs
        $recentAuditLogs = AuditLog::with('user', 'workstation')
            ->recent(24)
            ->orderBy('timestamp', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.index', [
            'floors' => $floors,
            'currentFloor' => $currentFloor,
            'bays' => $bays,
            'metrics' => $metrics,
            'recentAuditLogs' => $recentAuditLogs,
            'user' => $user,
            'allAssets' => Workstation::with('bay')->get()->map(fn($w) => [
                'id' => $w->id,
                'name' => $w->station_id,
                'type' => $w->type,
                'floor_id' => $w->bay->floor_id,
                'hostname' => $w->hostname,
                'ip' => $w->ip_address,
                'mac' => $w->mac_address,
                'x' => $w->x,
                'y' => $w->y,
            ]),
        ]);
    }

    /**
     * Store a new floor layout.
     */
    public function storeFloor(Request $request): JsonResponse
    {
        if (!auth()->user()->isItAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'campaign' => 'required|string|max:255',
        ]);

        $nextNumber = Floor::max('floor_number') + 1;

        $floor = Floor::create([
            'floor_number' => $nextNumber,
            'floor_name' => $validated['name'],
            'description' => $validated['campaign'],
        ]);

        return response()->json([
            'message' => 'Floor created successfully.',
            'floor' => [
                'id' => $floor->id,
                'name' => $floor->floor_name,
                'campaign' => $floor->description,
            ],
        ]);
    }

    /**
     * Update an existing floor.
     */
    public function updateFloor(Request $request, Floor $floor): JsonResponse
    {
        if (!auth()->user()->isItAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'campaign' => 'required|string|max:255',
        ]);

        $floor->update([
            'floor_name' => $validated['name'],
            'description' => $validated['campaign'],
        ]);

        return response()->json([
            'message' => 'Floor updated successfully.',
            'floor' => [
                'id' => $floor->id,
                'name' => $floor->floor_name,
                'campaign' => $floor->description,
            ],
        ]);
    }

    /**
     * Delete a floor.
     */
    public function destroyFloor(Floor $floor): JsonResponse
    {
        if (!auth()->user()->isItAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $floor->delete();

        return response()->json(['message' => 'Floor deleted successfully.']);
    }

    /**
     * Store a new workstation asset.
     */
    public function storeWorkstation(Request $request): JsonResponse
    {
        $this->authorize('create', Workstation::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'floor_id' => 'required|exists:floors,id',
            'hostname' => 'required|string|unique:workstations,hostname',
            'ip' => 'required|string|unique:workstations,ip_address',
            'mac' => 'nullable|string',
            'x' => 'required|integer',
            'y' => 'required|integer',
        ]);

        $bay = Bay::firstOrCreate(
            ['floor_id' => $validated['floor_id'], 'bay_letter' => 'A'],
            ['client_campaign_name' => 'Default']
        );

        $workstation = Workstation::create([
            'bay_id' => $bay->id,
            'station_id' => $validated['name'],
            'hostname' => $validated['hostname'],
            'ip_address' => $validated['ip'],
            'mac_address' => $validated['mac'],
            'type' => $validated['type'],
            'x' => $validated['x'],
            'y' => $validated['y'],
            'status' => 'empty',
        ]);

        return response()->json([
            'message' => 'Workstation deployed successfully.',
            'asset' => [
                'id' => $workstation->id,
                'name' => $workstation->station_id,
                'type' => $workstation->type,
                'floor_id' => $validated['floor_id'],
                'hostname' => $workstation->hostname,
                'ip' => $workstation->ip_address,
                'mac' => $workstation->mac_address,
                'x' => $workstation->x,
                'y' => $workstation->y,
            ],
        ]);
    }

    /**
     * Get workstation details for the sidebar.
     */
    public function getWorkstationDetails(Workstation $workstation): JsonResponse
    {
        $this->authorize('view', $workstation);

        return response()->json([
            'id' => $workstation->id,
            'station_id' => $workstation->station_id,
            'hostname' => $workstation->hostname,
            'ip_address' => $workstation->ip_address,
            'mac_address' => $workstation->mac_address,
            'status' => $workstation->status,
            'voice_vlan' => $workstation->voice_vlan,
            'data_vlan' => $workstation->data_vlan,
            'headset_serial' => $workstation->headset_serial,
            'agent_name' => $workstation->agent_name,
            'asset_tag' => $workstation->asset_tag,
            'last_ping_at' => $workstation->last_ping_at?->format('Y-m-d H:i:s'),
            'bay' => [
                'id' => $workstation->bay->id,
                'bay_letter' => $workstation->bay->bay_letter,
                'client_campaign_name' => $workstation->bay->client_campaign_name,
            ],
            'floor' => [
                'id' => $workstation->bay->floor->id,
                'floor_number' => $workstation->bay->floor->floor_number,
            ],
            'can_update' => auth()->user()->can('update', $workstation),
            'can_remote_session' => auth()->user()->can('remoteSession', $workstation),
            'svg_element_id' => $workstation->getSvgElementId(),
        ]);
    }

    /**
     * Search workstations globally.
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->query('term', '');

        if (strlen($term) < 2) {
            return response()->json([
                'results' => [],
                'message' => 'Please enter at least 2 characters.',
            ]);
        }

        $user = auth()->user();

        // Build base query
        $query = Workstation::search($term);

        // Restrict results based on user role
        if ($user->isOperationsManager() && $user->assigned_bay_id) {
            // Operations Manager can only search their assigned bay
            $query = $query->where('bay_id', $user->assigned_bay_id);
        }

        // Get results
        $results = $query->with('bay.floor')
            ->limit(20)
            ->get()
            ->map(function (Workstation $workstation) {
                return [
                    'id' => $workstation->id,
                    'hostname' => $workstation->hostname,
                    'ip_address' => $workstation->ip_address,
                    'agent_name' => $workstation->agent_name,
                    'asset_tag' => $workstation->asset_tag,
                    'status' => $workstation->status,
                    'station_id' => $workstation->station_id,
                    'floor_id' => $workstation->bay->floor->id,
                    'floor_number' => $workstation->bay->floor->floor_number,
                    'bay_letter' => $workstation->bay->bay_letter,
                    'campaign' => $workstation->bay->client_campaign_name,
                ];
            });

        return response()->json([
            'results' => $results,
            'total' => $results->count(),
        ]);
    }

    /**
     * Update workstation status/metadata.
     */
    public function updateWorkstation(Request $request, Workstation $workstation): JsonResponse
    {
        $this->authorize('update', $workstation);

        $validated = $request->validate([
            'name' => 'nullable|string',
            'type' => 'nullable|string',
            'hostname' => 'nullable|string|unique:workstations,hostname,' . $workstation->id,
            'ip' => 'nullable|string|unique:workstations,ip_address,' . $workstation->id,
            'mac' => 'nullable|string',
            'x' => 'nullable|integer',
            'y' => 'nullable|integer',
            'agent_name' => 'nullable|string|max:255',
            'asset_tag' => 'nullable|string|max:255',
            'headset_serial' => 'nullable|string|max:255',
            'status' => 'in:active,offline,empty',
            'notes' => 'nullable|string',
        ]);

        // Map JS keys to DB columns
        if (isset($validated['name'])) $validated['station_id'] = $validated['name'];
        if (isset($validated['ip'])) $validated['ip_address'] = $validated['ip'];
        if (isset($validated['mac'])) $validated['mac_address'] = $validated['mac'];

        $workstation->update($validated);

        // Track changes for audit log
        $changes = $workstation->getChanges();

        // Log the action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action_performed' => 'workstation_updated',
            'workstation_id' => $workstation->id,
            'affected_model' => 'Workstation',
            'affected_model_id' => $workstation->id,
            'changes' => array_merge($changes, $validated),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        return response()->json([
            'message' => 'Workstation updated successfully.',
            'workstation' => $workstation,
        ]);
    }

    /**
     * Delete a workstation.
     */
    public function destroyWorkstation(Workstation $workstation): JsonResponse
    {
        $this->authorize('delete', $workstation);

        $workstation->delete();

        return response()->json(['message' => 'Workstation deleted successfully.']);
    }

    /**
     * Trigger a remote session (RDP/VNC).
     */
    public function launchRemoteSession(Request $request, Workstation $workstation): JsonResponse
    {
        $this->authorize('remoteSession', $workstation);

        // Generate the RDP/VNC protocol URL
        $protocol = config('vxi.remote_protocol', 'rdp'); // 'rdp' or 'vnc'
        $remoteUrl = $this->generateRemoteUrl($workstation, $protocol);

        // Log the action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action_performed' => 'remote_session_initiated',
            'workstation_id' => $workstation->id,
            'affected_model' => 'Workstation',
            'affected_model_id' => $workstation->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        return response()->json([
            'protocol' => $protocol,
            'url' => $remoteUrl,
            'hostname' => $workstation->hostname,
            'message' => "Launching remote session to {$workstation->hostname}",
        ]);
    }

    /**
     * Get all workstation statuses for real-time updating.
     */
    public function getWorkstationStatuses(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Build base query
        $query = Workstation::with('bay.floor');

        // Restrict results based on user role
        if ($user->isOperationsManager() && $user->assigned_bay_id) {
            $query = $query->where('bay_id', $user->assigned_bay_id);
        }

        $workstations = $query->get()->map(function (Workstation $workstation) {
            return [
                'id' => $workstation->id,
                'svg_element_id' => $workstation->getSvgElementId(),
                'status' => $workstation->status,
                'status_class' => $workstation->getStatusClass(),
                'hostname' => $workstation->hostname,
            ];
        });

        return response()->json([
            'statuses' => $workstations,
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * Calculate metrics for a floor.
     */
    private function getFloorMetrics(Floor $floor): array
    {
        $totalWorkstations = $floor->workstations()->count();
        $activeWorkstations = $floor->activeWorkstationCount();
        $offlineWorkstations = $floor->offlineWorkstationCount();
        $emptyWorkstations = $floor->emptyWorkstationCount();

        $occupancyPercentage = $totalWorkstations > 0
            ? round(($activeWorkstations / $totalWorkstations) * 100, 2)
            : 0;

        return [
            'total_workstations' => $totalWorkstations,
            'active' => $activeWorkstations,
            'offline' => $offlineWorkstations,
            'empty' => $emptyWorkstations,
            'occupancy_percentage' => $occupancyPercentage,
        ];
    }

    /**
     * Generate a remote session URL.
     */
    private function generateRemoteUrl(Workstation $workstation, string $protocol): string
    {
        $hostname = $workstation->hostname;

        return match ($protocol) {
            'rdp' => "rdp://{$hostname}",
            'vnc' => "vnc://{$hostname}",
            default => "rdp://{$hostname}",
        };
    }
}
