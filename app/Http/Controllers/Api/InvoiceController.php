<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['contact', 'account', 'user']);
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_id'  => 'nullable|exists:contacts,id',
            'account_id'  => 'nullable|exists:accounts,id',
            'quote_id'    => 'nullable|exists:quotes,id',
            'user_id'     => 'nullable|exists:users,id',
            'status'      => 'in:draft,sent,paid,overdue,cancelled',
            'subtotal'    => 'required|numeric|min:0',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'amount_paid' => 'nullable|numeric|min:0',
            'currency'    => 'string|max:3',
            'notes'       => 'nullable|string',
            'due_date'    => 'nullable|date',
            'line_items'  => 'nullable|array',
        ]);

        $validated['reference'] = 'FAC-' . strtoupper(Str::random(6));
        $subtotal = $validated['subtotal'];
        $taxRate  = $validated['tax_rate'] ?? 0;
        $validated['tax_amount'] = round($subtotal * $taxRate / 100, 2);
        $validated['total']      = round($subtotal + $validated['tax_amount'], 2);

        $invoice = Invoice::create($validated);
        return response()->json($invoice->load(['contact', 'account', 'user']), 201);
    }

    public function show(Invoice $invoice)
    {
        return response()->json($invoice->load(['contact', 'account', 'quote', 'user']));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'contact_id'  => 'nullable|exists:contacts,id',
            'account_id'  => 'nullable|exists:accounts,id',
            'status'      => 'in:draft,sent,paid,overdue,cancelled',
            'subtotal'    => 'sometimes|numeric|min:0',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'amount_paid' => 'nullable|numeric|min:0',
            'currency'    => 'string|max:3',
            'notes'       => 'nullable|string',
            'due_date'    => 'nullable|date',
            'paid_at'     => 'nullable|date',
            'line_items'  => 'nullable|array',
        ]);

        if (isset($validated['subtotal'])) {
            $subtotal = $validated['subtotal'];
            $taxRate  = $validated['tax_rate'] ?? $invoice->tax_rate;
            $validated['tax_amount'] = round($subtotal * $taxRate / 100, 2);
            $validated['total']      = round($subtotal + $validated['tax_amount'], 2);
        }

        // Auto-set paid_at when status → paid
        if (isset($validated['status']) && $validated['status'] === 'paid' && !$invoice->paid_at) {
            $validated['paid_at'] = now()->toDateString();
        }

        $invoice->update($validated);
        return response()->json($invoice->load(['contact', 'account', 'user']));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->noContent();
    }
}
