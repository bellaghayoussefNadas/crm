<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Csv\Reader;
use League\Csv\Writer;

class ContactController extends Controller
{
    public function index()
    {
        return response()->json(Contact::with('account')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        $contact = Contact::create($validated);
        return response()->json($contact, 201);
    }

    public function show(Contact $contact)
    {
        return response()->json($contact->load('account'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        $contact->update($validated);
        return response()->json($contact);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->noContent();
    }

    public function export()
    {
        $contacts = Contact::with('account')->get();
        $csv = Writer::createFromString('');
        $csv->insertOne(['ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Poste', 'Entreprise']);
        foreach ($contacts as $c) {
            $csv->insertOne([$c->id, $c->first_name, $c->last_name, $c->email, $c->phone, $c->job_title, $c->account?->name]);
        }
        return response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        $csv = Reader::createFromPath($request->file('file')->getPathname());
        $csv->setHeaderOffset(0);
        $imported = 0; $skipped = 0;
        foreach ($csv->getRecords() as $row) {
            $email = trim($row['Email'] ?? $row['email'] ?? '');
            if ($email && Contact::where('email', $email)->exists()) { $skipped++; continue; }
            Contact::create([
                'first_name' => trim($row['Prénom'] ?? $row['first_name'] ?? ''),
                'last_name'  => trim($row['Nom'] ?? $row['last_name'] ?? ''),
                'email'      => $email ?: null,
                'phone'      => trim($row['Téléphone'] ?? $row['phone'] ?? '') ?: null,
                'job_title'  => trim($row['Poste'] ?? $row['job_title'] ?? '') ?: null,
            ]);
            $imported++;
        }
        return response()->json(['imported' => $imported, 'skipped' => $skipped]);
    }
}
