<?php

namespace App\Http\Controllers;

use App\Models\KanbanColumn;
use Illuminate\Http\Request;

class KanbanColumnController extends Controller
{
    public function update(Request $request)
    {
        $request->validate(['id' => 'required|exists:kanban_columns,id']);

        $column = KanbanColumn::findOrFail($request->input('id'));
        $column->update($request->except('id'));
        
        return response()->json($column);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:kanban_columns,id']);

        KanbanColumn::destroy($request->input('id'));
        
        return response()->json(['message' => 'Column deleted successfully']);
    }
}
