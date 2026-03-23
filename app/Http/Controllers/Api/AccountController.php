<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        return response()->json(Account::latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $account = Account::create($validated);
        return response()->json($account, 201);
    }

    public function show(Account $account)
    {
        return response()->json($account->load(['contacts', 'deals', 'activities']));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $account->update($validated);
        return response()->json($account);
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return response()->noContent();
    }
}
