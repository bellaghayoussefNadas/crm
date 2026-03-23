<x-mail::message>
# Bonjour {{ $quote->contact->first_name ?? 'Cher Client' }},

Nous avons le plaisir de vous adresser notre proposition commerciale relative à votre projet : **{{ $quote->title }}**.

**Détails du devis :**
*   Référence : {{ $quote->reference }}
*   Montant Total : **{{ number_format($quote->total, 2) }} {{ $quote->currency }}**
*   Valide jusqu'au : {{ $quote->valid_until ? date('d/m/Y', strtotime($quote->valid_until)) : 'N/A' }}

@if($quote->notes)
### Notes complémentaires :
{{ $quote->notes }}
@endif

<x-mail::button :url="'#'">
Voir les détails
</x-mail::button>

Merci de nous faire part de votre retour.

Cordialement,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
