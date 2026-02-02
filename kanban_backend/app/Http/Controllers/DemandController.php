<?php

namespace App\Http\Controllers;

use App\Models\Demand;
use Illuminate\Http\Request;

class DemandController extends Controller
{
    public function index()
    {
        return Demand::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'cliente' => 'required|exists:clients,id',
            'kanban_column_id' => 'required|exists:kanban_columns,id',
            // Add other validations as needed
        ]);

        // Merge defaults if necessary or rely on DB defaults
        $demand = Demand::create($request->all());
        return response()->json($demand, 201);
    }

    public function update(Request $request, $id)
    {
        $demand = Demand::findOrFail($id);
        $demand->update($request->all());
        return response()->json($demand);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        
        $demand = Demand::findOrFail($id);
        $demand->update(['status' => $request->input('status')]);
        return response()->json($demand);
    }

    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'position_in_column' => 'required|integer',
            'kanban_column_id' => 'nullable|exists:kanban_columns,id'
        ]);

        $demand = Demand::findOrFail($id);
        $demand->update($request->only(['position_in_column', 'kanban_column_id']));
        
        return response()->json($demand);
    }

    public function destroyMany(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        
        Demand::destroy($request->input('ids'));
        return response()->json(['message' => 'Demands deleted successfully']);
    }
}
