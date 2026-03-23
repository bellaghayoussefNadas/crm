<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Invoice;
use App\Mail\QuoteMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['contact', 'account', 'user']);
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'contact_id'   => 'nullable|exists:contacts,id',
            'account_id'   => 'nullable|exists:accounts,id',
            'deal_id'      => 'nullable|exists:deals,id',
            'user_id'      => 'nullable|exists:users,id',
            'status'       => 'in:draft,sent,accepted,rejected,expired',
            'subtotal'     => 'required|numeric|min:0',
            'tax_rate'     => 'nullable|numeric|min:0|max:100',
            'currency'     => 'string|max:3',
            'notes'        => 'nullable|string',
            'valid_until'  => 'nullable|date',
            'line_items'   => 'nullable|array',
        ]);

        $validated['reference'] = 'DEV-' . strtoupper(Str::random(6));
        $subtotal = $validated['subtotal'];
        $taxRate  = $validated['tax_rate'] ?? 0;
        $validated['tax_amount'] = round($subtotal * $taxRate / 100, 2);
        $validated['total']      = round($subtotal + $validated['tax_amount'], 2);

        $quote = Quote::create($validated);
        
        if ($quote->status === 'sent') {
            $this->sendQuoteEmail($quote);
        }

        return response()->json($quote->load(['contact', 'account', 'user']), 201);
    }

    public function show(Quote $quote)
    {
        return response()->json($quote->load(['contact', 'account', 'deal', 'user']));
    }

    public function update(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'title'        => 'sometimes|required|string|max:255',
            'contact_id'   => 'nullable|exists:contacts,id',
            'account_id'   => 'nullable|exists:accounts,id',
            'deal_id'      => 'nullable|exists:deals,id',
            'user_id'      => 'nullable|exists:users,id',
            'status'       => 'in:draft,sent,accepted,rejected,expired',
            'subtotal'     => 'sometimes|numeric|min:0',
            'tax_rate'     => 'nullable|numeric|min:0|max:100',
            'currency'     => 'string|max:3',
            'notes'        => 'nullable|string',
            'valid_until'  => 'nullable|date',
            'line_items'   => 'nullable|array',
        ]);

        if (isset($validated['subtotal'])) {
            $subtotal = $validated['subtotal'];
            $taxRate  = $validated['tax_rate'] ?? $quote->tax_rate;
            $validated['tax_amount'] = round($subtotal * $taxRate / 100, 2);
            $validated['total']      = round($subtotal + $validated['tax_amount'], 2);
        }

        $oldStatus = $quote->status;
        $quote->update($validated);

        if ($quote->status === 'sent' && $oldStatus !== 'sent') {
            $this->sendQuoteEmail($quote);
        }

        return response()->json($quote->load(['contact', 'account', 'user']));
    }

    private function sendQuoteEmail(Quote $quote)
    {
        $email = $quote->contact->email ?? $quote->account->email ?? null;
        if ($email) {
            Mail::to($email)->send(new QuoteMail($quote));
        }
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return response()->noContent();
    }

    /** Convert quote → invoice */
    public function convertToInvoice(Quote $quote)
    {
        $invoice = Invoice::create([
            'reference'   => 'FAC-' . strtoupper(Str::random(6)),
            'contact_id'  => $quote->contact_id,
            'account_id'  => $quote->account_id,
            'quote_id'    => $quote->id,
            'user_id'     => $quote->user_id,
            'status'      => 'draft',
            'subtotal'    => $quote->subtotal,
            'tax_rate'    => $quote->tax_rate,
            'tax_amount'  => $quote->tax_amount,
            'total'       => $quote->total,
            'currency'    => $quote->currency,
            'notes'       => $quote->notes,
            'line_items'  => $quote->line_items,
            'due_date'    => now()->addDays(30)->toDateString(),
        ]);

        $quote->update(['status' => 'accepted']);

        return response()->json($invoice->load(['contact', 'account', 'quote']), 201);
    }
}
