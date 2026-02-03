<?php

namespace App\Http\Controllers;

use App\Models\Demand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DemandController extends Controller
{
    public function index()
    {
        return Demand::all();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'cliente' => 'required|exists:clients,id',
            'kanban_column_id' => 'required|exists:kanban_columns,id',
            'position_in_column' => 'required|integer',
            'priority_table_id' => 'required|exists:priorities,id',
            'department_table_id' => 'nullable|exists:departments,id',
            'responsavel' => 'required|string',
            'quem_deve_testar' => 'nullable|string',
            'descricao_detalhada' => 'nullable|string',
            'tempo_estimado' => 'nullable|numeric',
            'cobrada_do_cliente' => 'sometimes|boolean',
            'flag_returned' => 'sometimes|boolean',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        // Ensure booleans are correctly cast
        $data['cobrada_do_cliente'] = $request->boolean('cobrada_do_cliente');
        $data['flag_returned'] = $request->boolean('flag_returned');

        $demand = Demand::create($data);
        $demand->refresh(); // Load relations defined in $with

        return response()->json($demand, 201);
    }

    public function update(Request $request, $id)
    {
        $demand = Demand::findOrFail($id);
        
        // We use $request->all() here but you might want to validate similar to store
        // For simplicity in this update, we'll just update the fields present
        $data = $request->all();
        if ($request->has('cobrada_do_cliente')) $data['cobrada_do_cliente'] = $request->boolean('cobrada_do_cliente');
        if ($request->has('flag_returned')) $data['flag_returned'] = $request->boolean('flag_returned');

        $demand->update($data);
        $demand->refresh();
        
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

    public function reorder(Request $request)
    {
        $request->validate([
            'demands' => 'required|array',
            'demands.*.id' => 'required|exists:demands,id',
            'demands.*.position_in_column' => 'required|integer',
            'demands.*.kanban_column_id' => 'nullable|exists:kanban_columns,id',
        ]);

        $demandsData = $request->input('demands');

        DB::transaction(function () use ($demandsData) {
            // 1. Temporarily set positions to a negative value based on ID to avoid unique constraint collisions
            // during the swap process.
            foreach ($demandsData as $data) {
                Demand::where('id', $data['id'])->update(['position_in_column' => -1 * $data['id']]);
            }

            // 2. Set the actual new positions and columns
            foreach ($demandsData as $data) {
                $updateData = ['position_in_column' => $data['position_in_column']];
                if (isset($data['kanban_column_id'])) {
                    $updateData['kanban_column_id'] = $data['kanban_column_id'];
                }
                Demand::where('id', $data['id'])->update($updateData);
            }
        });

        return response()->noContent();
    }

    public function destroy(Demand $demand)
    {
        $demand->delete();
        return response()->noContent();
    }
}
