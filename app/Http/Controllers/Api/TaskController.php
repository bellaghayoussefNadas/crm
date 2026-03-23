<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->latest()->paginate(10);
        return TaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:pending,in_progress,completed',
            'user_id' => 'required|exists:users,id'
        ]);

        $task = Task::create($validated);
        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        return new TaskResource($task->load('user'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:pending,in_progress,completed',
            'user_id' => 'sometimes|required|exists:users,id'
        ]);

        $task->update($validated);
        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->noContent();
    }
}
