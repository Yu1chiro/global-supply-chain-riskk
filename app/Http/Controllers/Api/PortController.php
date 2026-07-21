<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\Request;

// Kelas PortController: port controller
class PortController extends Controller
{
    // index
    public function index(Request $request)
    {
        $query = Port::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('country', 'LIKE', "%{$search}%")
                ->orWhere('code', 'LIKE', "%{$search}%");
        }

        if ($request->has('country')) {
            $query->where('country', 'LIKE', "%{$request->country}%");
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        
        
        if ($request->has('port_size')) {
            $query->whereJsonContains('metadata->port_size', $request->port_size);
        }

        
        
        
        $perPage = min((int) $request->get('per_page', 50), 1000);
        $ports = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $ports->items(),
            'total' => $ports->total(),
            'current_page' => $ports->currentPage(),
            'last_page' => $ports->lastPage(),
        ]);
    }

    // store
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'country' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'code' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $port = Port::create($validated);
        return response()->json([
            'success' => true,
            'data' => $port,
        ], 201);
    }

    // show
    public function show(int $id)
    {
        $port = Port::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $port,
        ]);
    }

    // update
    public function update(Request $request, int $id)
    {
        $port = Port::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string',
            'country' => 'string',
            'latitude' => 'numeric|between:-90,90',
            'longitude' => 'numeric|between:-180,180',
            'status' => 'in:active,inactive,maintenance',
        ]);

        $port->update($validated);
        return response()->json([
            'success' => true,
            'data' => $port,
        ]);
    }

    // destroy
    public function destroy(int $id)
    {
        $port = Port::findOrFail($id);
        $port->delete();
        return response()->json([
            'success' => true,
            'message' => 'Port deleted successfully'
        ]);
    }

    // get all ports geo json
    public function getAllPortsGeoJson()
    {
        $ports = Port::all();
        $features = [];

        foreach ($ports as $port) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $port->longitude, (float) $port->latitude],
                ],
                'properties' => [
                    'id' => $port->id,
                    'name' => $port->name,
                    'country' => $port->country,
                    'type' => $port->type,
                    'status' => $port->status,
                ],
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}

