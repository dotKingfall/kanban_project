<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->clients;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email',
            'whatsapp' => 'nullable|string',
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
        $client->update($request->all());
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
        // get/reports/clients?ids[]=1&ids[]=2&month=YYYY-MM
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'month' => 'required|date_format:Y-m',
        ]);

        // TODO include demands/stats for the specific month
        $clients = Client::whereIn('id', $request->input('ids'))->get();

        return response()->json([
            'month' => $request->input('month'),
            'clients' => $clients,
        ]);
    }
}
