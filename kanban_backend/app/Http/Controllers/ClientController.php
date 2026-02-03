<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Demand;
use App\Models\KanbanColumn;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('client_id')) {
            return $this->show($request, $request->input('client_id'));
        }

        $clients = $request->user()->clients;

        // Eager load demands for all clients to improve frontend performance
        // We fetch all demands belonging to these clients and group them by client ID
        $clientIds = $clients->pluck('id');
        $demands = Demand::whereIn('cliente', $clientIds)->get()->groupBy('cliente');
        $columns = KanbanColumn::whereIn('client_id', $clientIds)->orderBy('position')->get()->groupBy('client_id');

        foreach ($clients as $client) {
            $client->setRelation('demands', $demands->get($client->id, []));
            $client->setRelation('kanban_columns', $columns->get($client->id, []));
        }

        return $clients;
    }

    public function show(Request $request, $id)
    {
        $client = $request->user()->clients()->findOrFail($id);
        // Fetch demands associated with this client
        $demands = Demand::where('cliente', $id)->get();
        $columns = KanbanColumn::where('client_id', $id)->orderBy('position')->get();

        $client->setRelation('demands', $demands);
        $client->setRelation('kanban_columns', $columns);
        
        return response()->json($client);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|required_without:whatsapp',
            'whatsapp' => 'nullable|string|required_without:email',
            'avisar_por_email' => 'boolean',
            'avisar_por_whatsapp' => 'boolean',
            'observacao' => 'nullable|string',
            'reverse_filter' => 'boolean',
            'global_filter_id' => 'nullable|exists:demand_filter_types,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $client = Client::create($validated);
        return response()->json($client, 201);
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|required_without:whatsapp',
            'whatsapp' => 'nullable|string|required_without:email',
            'avisar_por_email' => 'boolean',
            'avisar_por_whatsapp' => 'boolean',
            'observacao' => 'nullable|string',
            'reverse_filter' => 'boolean',
            'global_filter_id' => 'nullable|exists:demand_filter_types,id',
        ]);

        $client->update($validated);
        return response()->json($client);
    }

    public function destroyMany(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:clients,id',
        ]);

        Client::destroy($request->input('ids'));
        return response()->json(['message' => 'Clients deleted successfully']);
    }

    public function reports(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'integer',
        'month' => 'required|date_format:Y-m',
    ]);

    $clientIds = $request->input('ids');
    $month = $request->input('month'); // "YYYY-MM"

    // Fetch demands for these clients created in the specific month
    $demands = Demand::whereIn('cliente', $clientIds)
        ->whereRaw("to_char(created_at, 'YYYY-MM') = ?", [$month])
        ->get();

    // Stats calculations
    $stats = [
        'total' => $demands->count(),
        'billed' => $demands->where('cobrada_do_cliente', true)->count(),
        'by_status' => $demands->groupBy('status')->map(fn($group) => $group->count()),
    ];

    return response()->json([
        'month' => $month,
        'demands' => $demands,
        'stats' => $stats
    ]);
}

    public function updateColumns(Request $request)
    {
        $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|exists:kanban_columns,id',
            'columns.*.position' => 'required|integer',
            'columns.*.is_fixed' => 'boolean',
            'columns.*.is_hidden' => 'boolean',
        ]);

        foreach ($request->input('columns') as $colData) {
            KanbanColumn::where('id', $colData['id'])->update([
                'position' => $colData['position'],
                'is_fixed' => $colData['is_fixed'],
                'is_hidden' => $colData['is_hidden'],
            ]);
        }

        return response()->json(['message' => 'Columns updated successfully']);
    }
}
