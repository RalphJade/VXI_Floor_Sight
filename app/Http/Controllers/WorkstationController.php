<?php
namespace App\Http\Controllers;

use App\Models\Workstation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkstationController extends Controller
{
    /**
     * Return a JSON list of workstations.
     * If a floorId is supplied, only workstations belonging to that floor are returned.
     */
    public function index(?int $floorId = null): JsonResponse
    {
        $query = Workstation::query()->select([
            'id', 'floor_id', 'name', 'type', 'hostname', 'ip', 'mac', 'status', 'x', 'y'
        ]);
        if ($floorId !== null) {
            $query->where('floor_id', $floorId);
        }
        $workstations = $query->orderBy('id')->get();
        return response()->json($workstations);
    }

    /**
     * Update mutable fields of a workstation (e.g. x, y coordinates).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $ws = Workstation::findOrFail($id);
        // Only allow specific fields to be mass‑assigned for safety.
        $data = $request->only(['x', 'y', 'name', 'type', 'hostname', 'ip', 'mac', 'status']);
        $ws->update($data);
        return response()->json($ws);
    }
}
?>
