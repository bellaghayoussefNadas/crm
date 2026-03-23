<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index()
    {
        return response()->json(Deal::with(['account', 'contact'])->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
            'currency' => 'string|max:3',
            'stage' => 'required|string',
            'probability' => 'integer|min:0|max:100',
            'account_id' => 'nullable|exists:accounts,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'expected_close_date' => 'nullable|date',
        ]);

        $deal = Deal::create($validated);
        return response()->json($deal, 201);
    }

    public function show(Deal $deal)
    {
        return response()->json($deal->load(['account', 'contact']));
    }

    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'amount' => 'nullable|numeric',
            'currency' => 'string|max:3',
            'stage' => 'sometimes|required|string',
            'probability' => 'integer|min:0|max:100',
            'account_id' => 'nullable|exists:accounts,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'expected_close_date' => 'nullable|date',
        ]);

        $deal->update($validated);
        return response()->json($deal);
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();
        return response()->noContent();
    }
}
