<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Http\Resources\LeadResource;
use Illuminate\Http\Request;
use League\Csv\Reader;
use League\Csv\Writer;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $leads = $query->latest()->paginate(10);
        return LeadResource::collection($leads);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'status' => 'nullable|in:new,contacted,qualified,lost,won',
            'user_id' => 'nullable|exists:users,id'
        ]);

        // Auto distribution to user with fewest leads if not specified
        if (empty($validated['user_id'])) {
            $validated['user_id'] = User::withCount('leads')->orderBy('leads_count', 'asc')->first()?->id;
        }

        // Basic lead score
        $validated['score'] = $this->calculateScore($validated);

        $lead = Lead::create($validated);
        return new LeadResource($lead);
    }

    private function calculateScore($data)
    {
        $score = 10; // base score
        if (!empty($data['email']))   $score += 20;
        if (!empty($data['phone']))   $score += 20;
        if (!empty($data['company'])) $score += 15;
        if (!empty($data['source']) && $data['source'] === 'referral') $score += 20;
        return min(100, $score);
    }

    public function export()
    {
        $leads = Lead::with('user')->get();
        $csv = Writer::createFromString('');
        $csv->insertOne(['ID', 'Nom', 'Email', 'Téléphone', 'Société', 'Statut', 'Commercial', 'Score']);
        foreach ($leads as $l) {
            $csv->insertOne([$l->id, $l->name, $l->email, $l->phone, $l->company, $l->status, $l->user?->name, $l->score]);
        }
        return response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        $csv = Reader::createFromPath($request->file('file')->getPathname());
        $csv->setHeaderOffset(0);
        $imported = 0;
        foreach ($csv->getRecords() as $row) {
            $data = [
                'name'    => trim($row['Nom'] ?? $row['name'] ?? ''),
                'email'   => trim($row['Email'] ?? $row['email'] ?? ''),
                'phone'   => trim($row['Téléphone'] ?? $row['phone'] ?? '') ?: null,
                'company' => trim($row['Société'] ?? $row['company'] ?? '') ?: null,
                'status'  => 'new',
            ];
            if (empty($data['name'])) continue;
            $data['score'] = $this->calculateScore($data);
            Lead::create($data);
            $imported++;
        }
        return response()->json(['imported' => $imported]);
    }

    public function show(Lead $lead)
    {
        return new LeadResource($lead->load(['user', 'activities']));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'status' => 'nullable|in:new,contacted,qualified,lost,won',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $lead->update($validated);
        return new LeadResource($lead);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return response()->noContent();
    }
}
