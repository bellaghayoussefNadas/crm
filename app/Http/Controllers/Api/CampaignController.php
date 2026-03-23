<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Contact;
use App\Mail\CampaignMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with('user');
        if ($request->status) $query->where('status', $request->status);
        if ($request->type)   $query->where('type', $request->type);
        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'type'         => 'required|in:email,sms,newsletter',
            'subject'      => 'nullable|string|max:255',
            'body'         => 'nullable|string',
            'status'       => 'in:draft,scheduled,sent,cancelled',
            'user_id'      => 'nullable|exists:users,id',
            'scheduled_at' => 'nullable|date',
        ]);

        $campaign = Campaign::create($validated);
        return response()->json($campaign->load('user'), 201);
    }

    public function show(Campaign $campaign)
    {
        return response()->json($campaign->load('user'));
    }

    public function send(Campaign $campaign)
    {
        $contacts = Contact::whereNotNull('email')->get();
        if ($contacts->isEmpty()) {
            return response()->json(['message' => 'Aucun contact avec email trouvé'], 422);
        }

        foreach ($contacts as $contact) {
            Mail::to($contact->email)->send(new CampaignMail($campaign->subject, $campaign->body));
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'recipients_count' => $contacts->count()
        ]);

        return response()->json(['message' => 'Campagne envoyée avec succès', 'count' => $contacts->count()]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'type'         => 'in:email,sms,newsletter',
            'subject'      => 'nullable|string|max:255',
            'body'         => 'nullable|string',
            'status'       => 'in:draft,scheduled,sent,cancelled',
            'scheduled_at' => 'nullable|date',
        ]);

        $campaign->update($validated);
        return response()->json($campaign->load('user'));
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return response()->noContent();
    }
}
