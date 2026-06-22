<?php

namespace App\Http\Controllers\Api;

use App\Models\Floor;
use App\Models\Workstation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FloorSightApiController
{
    public function index(): JsonResponse
    {
        $floors = Floor::with('workstations')
            ->orderBy('id')
            ->get()
            ->map(fn (Floor $floor) => [
                'id' => $floor->id,
                'name' => $floor->name,
                'campaign' => $floor->campaign,
                'subnet' => $floor->subnet,
                'vlan_a' => $floor->vlan_a,
                'vlan_b' => $floor->vlan_b,
                'vlan_c' => $floor->vlan_c,
                'workstations' => $floor->workstations
                    ->orderBy('id')
                    ->map(fn (Workstation $w) => [
                        'id' => $w->id,
                        'floor_id' => $w->floor_id,
                        'name' => $w->name,
                        'type' => $w->type,
                        'hostname' => $w->hostname,
                        'ip' => $w->ip,
                        'mac' => $w->mac,
                        'status' => $w->status,
                        'agent' => $w->agent,
                        'x' => (int) $w->x,
                        'y' => (int) $w->y,
                    ]),
            ]);

        return response()->json(['floors' => $floors]);
    }

    public function storeFloor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'campaign' => 'required|string|max:255',
            'subnet' => 'nullable|string|max:64',
            'vlan_a' => 'nullable|string|max:64',
            'vlan_b' => 'nullable|string|max:64',
            'vlan_c' => 'nullable|string|max:64',
        ]);

        $floor = Floor::create($validated);

        return response()->json([
            'message' => 'Floor created successfully.',
            'floor' => [
                'id' => $floor->id,
                'name' => $floor->name,
                'campaign' => $floor->campaign,
                'subnet' => $floor->subnet,
                'vlan_a' => $floor->vlan_a,
                'vlan_b' => $floor->vlan_b,
                'vlan_c' => $floor->vlan_c,
            ],
        ], 201);
    }

    public function updateFloor(Request $request, Floor $floor): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'campaign' => 'required|string|max:255',
            'subnet' => 'nullable|string|max:64',
            'vlan_a' => 'nullable|string|max:64',
            'vlan_b' => 'nullable|string|max:64',
            'vlan_c' => 'nullable|string|max:64',
        ]);

        $floor->update($validated);

        return response()->json([
            'message' => 'Floor updated successfully.',
            'floor' => [
                'id' => $floor->id,
                'name' => $floor->name,
                'campaign' => $floor->campaign,
                'subnet' => $floor->subnet,
                'vlan_a' => $floor->vlan_a,
                'vlan_b' => $floor->vlan_b,
                'vlan_c' => $floor->vlan_c,
            ],
        ]);
    }

    public function destroyFloor(Floor $floor): JsonResponse
    {
        $floor->delete();

        return response()->json(['message' => 'Floor deleted successfully.']);
    }

    public function storeWorkstation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:agent,support,om',
            'hostname' => 'required|string|max:255|unique:workstations,hostname',
            'ip' => 'required|string|max:64|unique:workstations,ip',
            'mac' => 'nullable|string|max:64',
            'status' => 'nullable|in:active,alert,empty',
            'agent' => 'nullable|string|max:255',
            'x' => 'nullable|integer',
            'y' => 'nullable|integer',
        ]);

        $workstation = Workstation::create([
            'floor_id' => (int) $validated['floor_id'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'hostname' => $validated['hostname'],
            'ip' => $validated['ip'],
            'mac' => $validated['mac'] ?? null,
            'status' => $validated['status'] ?? 'empty',
            'agent' => $validated['agent'] ?? 'Unassigned Station',
            'x' => (int) ($validated['x'] ?? 100),
            'y' => (int) ($validated['y'] ?? 100),
        ]);

        return response()->json([
            'message' => 'Workstation deployed successfully.',
            'asset' => $this->mapWorkstation($workstation),
        ], 201);
    }

    public function updateWorkstation(Request $request, Workstation $workstation): JsonResponse
    {
        $validated = $request->validate([
            'floor_id' => 'sometimes|exists:floors,id',
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:agent,support,om',
            'hostname' => 'sometimes|required|string|max:255|unique:workstations,hostname,' . $workstation->id,
            'ip' => 'sometimes|required|string|max:64|unique:workstations,ip,' . $workstation->id,
            'mac' => 'nullable|string|max:64',
            'status' => 'nullable|in:active,alert,empty',
            'agent' => 'nullable|string|max:255',
            'x' => 'sometimes|integer',
            'y' => 'sometimes|integer',
        ]);

        $workstation->update([
            'floor_id' => isset($validated['floor_id']) ? (int) $validated['floor_id'] : $workstation->floor_id,
            'name' => $validated['name'] ?? $workstation->name,
            'type' => $validated['type'] ?? $workstation->type,
            'hostname' => $validated['hostname'] ?? $workstation->hostname,
            'ip' => $validated['ip'] ?? $workstation->ip,
            'mac' => array_key_exists('mac', $validated) ? ($validated['mac'] ?? null) : $workstation->mac,
            'status' => $validated['status'] ?? $workstation->status,
            'agent' => $validated['agent'] ?? $workstation->agent,
            'x' => array_key_exists('x', $validated) ? (int) $validated['x'] : $workstation->x,
            'y' => array_key_exists('y', $validated) ? (int) $validated['y'] : $workstation->y,
        ]);

        return response()->json([
            'message' => 'Workstation updated successfully.',
            'asset' => $this->mapWorkstation($workstation),
        ]);
    }

    public function destroyWorkstation(Workstation $workstation): JsonResponse
    {
        $workstation->delete();

        return response()->json(['message' => 'Workstation deleted successfully.']);
    }

    private function mapWorkstation(Workstation $w): array
    {
        return [
            'id' => $w->id,
            'floor_id' => (int) $w->floor_id,
            'name' => $w->name,
            'type' => $w->type,
            'hostname' => $w->hostname,
            'ip' => $w->ip,
            'mac' => $w->mac,
            'status' => $w->status,
            'agent' => $w->agent,
            'x' => (int) $w->x,
            'y' => (int) $w->y,
        ];
    }
}

