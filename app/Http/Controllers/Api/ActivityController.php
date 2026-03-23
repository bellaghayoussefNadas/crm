<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Http\Resources\ActivityResource;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['user', 'client', 'lead']);

        $activities = $query->latest()->paginate(10);
        return ActivityResource::collection($activities);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:call,email,meeting',
            'description' => 'required|string',
            'date' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'lead_id' => 'nullable|exists:leads,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $activity = Activity::create($validated);
        return new ActivityResource($activity);
    }

    public function show(Activity $activity)
    {
        return new ActivityResource($activity->load(['user', 'client', 'lead']));
    }

    public function update(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'type' => 'sometimes|required|in:call,email,meeting',
            'description' => 'sometimes|required|string',
            'date' => 'sometimes|required|date',
            'client_id' => 'nullable|exists:clients,id',
            'lead_id' => 'nullable|exists:leads,id',
            'user_id' => 'sometimes|required|exists:users,id'
        ]);

        $activity->update($validated);
        return new ActivityResource($activity);
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        return response()->noContent();
    }
}
