<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['contact', 'account', 'assignedTo', 'createdBy']);
        if ($request->status)   $query->where('status', $request->status);
        if ($request->priority) $query->where('priority', $request->priority);
        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'     => 'required|string|max:255',
            'description' => 'required|string',
            'contact_id'  => 'nullable|exists:contacts,id',
            'account_id'  => 'nullable|exists:accounts,id',
            'assigned_to' => 'nullable|exists:users,id',
            'created_by'  => 'nullable|exists:users,id',
            'status'      => 'in:open,in_progress,resolved,closed',
            'priority'    => 'in:low,medium,high,urgent',
            'category'    => 'in:technical,billing,general,other',
        ]);

        $ticket = Ticket::create($validated);
        return response()->json($ticket->load(['contact', 'assignedTo']), 201);
    }

    public function show(Ticket $ticket)
    {
        return response()->json($ticket->load(['contact', 'account', 'assignedTo', 'createdBy']));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'subject'     => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'contact_id'  => 'nullable|exists:contacts,id',
            'account_id'  => 'nullable|exists:accounts,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status'      => 'in:open,in_progress,resolved,closed',
            'priority'    => 'in:low,medium,high,urgent',
            'category'    => 'in:technical,billing,general,other',
            'resolution'  => 'nullable|string',
        ]);

        if (isset($validated['status']) && in_array($validated['status'], ['resolved', 'closed']) && !$ticket->resolved_at) {
            $validated['resolved_at'] = now();
        }

        $ticket->update($validated);
        return response()->json($ticket->load(['contact', 'account', 'assignedTo']));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->noContent();
    }
}
