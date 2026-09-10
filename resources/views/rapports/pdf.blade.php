<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        .entete { text-align: center; border-bottom: 3px solid #2563EB; padding-bottom: 8px; margin-bottom: 14px; }
        .entete h1 { margin: 0; font-size: 15px; color: #123A7A; }
        .entete p { margin: 2px 0; font-size: 10px; }
        .synthese { margin-bottom: 12px; }
        .synthese span { display: inline-block; margin-right: 14px; padding: 3px 8px; background: #E8F0FE; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px 5px; text-align: left; }
        th { background: #123A7A; color: #fff; font-size: 9px; }
        tr:nth-child(even) td { background: #fafafa; }
        .pied { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #888; }
    </style>
</head>
<body>
    <div class="entete">
        <h1>{{ \App\Support\Parametres::chaine('general.nom_bibliotheque', 'Bibliothèque Universitaire') }}</h1>
        <p>{{ \App\Support\Parametres::chaine('general.universite', '') }}</p>
        <p><strong>{{ $titre }}</strong> — {{ $periode }}</p>
    </div>

    <div class="synthese">
        @foreach($statistiques as $libelle => $valeur)
            <span><strong>{{ $libelle }} :</strong> {{ $valeur }}</span>
        @endforeach
    </div>

    <table>
        <thead><tr>@foreach($colonnes as $entete)<th>{{ $entete }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse($lignes as $ligne)
                <tr>@foreach(array_keys($colonnes) as $cle)<td>{{ $ligne[$cle] ?? '—' }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($colonnes) }}">Aucune donnée pour ces critères.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pied">
        Édité le {{ now()->format('d/m/Y à H:i') }} — {{ $lignes->count() }} ligne(s)
    </div>
</body>
</html>
