<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .entete { text-align: center; border-bottom: 3px solid #2563EB; padding-bottom: 10px; margin-bottom: 18px; }
        .entete h1 { margin: 0; font-size: 18px; color: #123A7A; }
        .entete p { margin: 2px 0; font-size: 11px; }
        h2 { font-size: 14px; margin: 18px 0 8px; color: #123A7A; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #E8F0FE; }
        .total { font-weight: bold; background: #f9f9f9; }
        .pied { margin-top: 30px; font-size: 10px; text-align: center; color: #777; }
    </style>
</head>
<body>
    @php $devise = \App\Support\Parametres::devise(); @endphp

    <div class="entete">
        <h1>{{ \App\Support\Parametres::chaine('general.nom_bibliotheque', 'Bibliothèque Universitaire') }}</h1>
        <p>{{ \App\Support\Parametres::chaine('general.universite', '') }}</p>
        <p>Reçu de pénalité n° {{ $penalite->id }} — édité le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <h2>Usager</h2>
    <table>
        <tr><th style="width:35%;">Nom</th><td>{{ $penalite->user?->name }}</td></tr>
        <tr><th>Matricule</th><td>{{ $penalite->user?->matricule }}</td></tr>
        <tr><th>Email</th><td>{{ $penalite->user?->email }}</td></tr>
    </table>

    <h2>Pénalité</h2>
    <table>
        <tr><th style="width:35%;">Type</th><td>{{ $penalite->libelle_type }}</td></tr>
        <tr><th>Motif</th><td>{{ $penalite->motif }}</td></tr>
        @if($penalite->emprunt)
            <tr><th>Ouvrage</th><td>{{ $penalite->emprunt->livre?->titre }}</td></tr>
        @endif
        @if($penalite->jours_retard)
            <tr><th>Jours de retard</th><td>{{ $penalite->jours_retard }}</td></tr>
        @endif
        <tr><th>Montant</th><td>{{ number_format((float) $penalite->montant, 0, ',', ' ') }} {{ $devise }}</td></tr>
        <tr><th>Statut</th><td>{{ $penalite->libelle_statut }}</td></tr>
    </table>

    <h2>Paiements</h2>
    <table>
        <thead><tr><th>Date</th><th>Montant</th><th>Mode</th><th>Référence</th><th>Encaissé par</th></tr></thead>
        <tbody>
            @forelse($penalite->paiements as $paiement)
                <tr>
                    <td>{{ $paiement->date_paiement->format('d/m/Y H:i') }}</td>
                    <td>{{ number_format((float) $paiement->montant, 0, ',', ' ') }} {{ $devise }}</td>
                    <td>{{ \App\Models\PaiementPenalite::MODES[$paiement->mode_paiement] ?? $paiement->mode_paiement }}</td>
                    <td>{{ $paiement->reference ?? '—' }}</td>
                    <td>{{ $paiement->caissier?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Aucun paiement enregistré.</td></tr>
            @endforelse
            <tr class="total">
                <td>Total payé</td>
                <td colspan="4">{{ number_format((float) $penalite->montant_paye, 0, ',', ' ') }} {{ $devise }}
                    — reste à payer : {{ number_format($penalite->estSoldee() ? 0 : $penalite->reste_a_payer, 0, ',', ' ') }} {{ $devise }}</td>
            </tr>
        </tbody>
    </table>

    <div class="pied">
        Document généré automatiquement — {{ \App\Support\Parametres::chaine('general.email_contact', '') }}
    </div>
</body>
</html>
