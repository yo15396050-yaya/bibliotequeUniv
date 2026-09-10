<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fiche d'Activité Étudiant</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #2563EB; padding-bottom: 15px; margin-bottom: 30px; }
        .header h1 { color: #123A7A; font-size: 20px; margin: 0; text-transform: uppercase; }
        .header p { color: #64748B; font-size: 11px; margin-top: 5px; }
        
        .student-info { margin-bottom: 40px; background-color: #fcf8f2; padding: 20px; border-radius: 8px; border: 1px solid #eaddca; }
        .student-name { font-size: 18px; color: #123A7A; font-weight: bold; margin-bottom: 10px; }
        .info-grid { width: 100%; border-collapse: collapse; }
        .info-grid td { padding: 5px; font-size: 11px; }
        .label { color: #64748B; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        
        .section-title { font-size: 14px; font-weight: bold; color: #123A7A; border-bottom: 1px solid #2563EB; margin: 30px 0 15px; padding-bottom: 5px; }
        
        table.loans { width: 100%; border-collapse: collapse; }
        table.loans th { background-color: #123A7A; color: #fff; text-align: left; padding: 10px; font-size: 10px; text-transform: uppercase; }
        table.loans td { border-bottom: 1px solid #eee; padding: 10px; font-size: 10px; vertical-align: middle; }
        
        .status-badge { padding: 3px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .status-rendu { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .status-en-cours { background-color: #fff8e1; color: #f57f17; border: 1px solid #ffecb3; }
        .status-en-retard { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Fiche d'Activité de la Bibliothèque</h1>
        <p>Générée le : {{ $date }} | État des emprunts</p>
    </div>

    <div class="student-info">
        <div class="student-name">{{ $etudiant->name }}</div>
        <table class="info-grid">
            <tr>
                <td width="50%"><span class="label">Matricule :</span><br> {{ $etudiant->matricule }}</td>
                <td width="50%"><span class="label">Email :</span><br> {{ $etudiant->email }}</td>
            </tr>
            <tr>
                <td width="50%"><span class="label">Filière / Niveau :</span><br> {{ $etudiant->filiere ?? 'N/A' }} ({{ $etudiant->niveau ?? 'N/A' }})</td>
                <td width="50%"><span class="label">Compte :</span><br> {{ ($etudiant->actif ?? true) ? 'ACTIF' : 'SUSPENDU' }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Historique Complet des Emprunts</div>
    
    <table class="loans">
        <thead>
            <tr>
                <th width="15%">ISBN</th>
                <th width="40%">Titre du Livre</th>
                <th width="15%">Sortie</th>
                <th width="15%">Retour</th>
                <th width="15%">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($etudiant->emprunts as $emprunt)
                <tr>
                    <td>{{ $emprunt->livre->isbn }}</td>
                    <td><strong>{{ $emprunt->livre->titre }}</strong><br><small>{{ $emprunt->livre->auteur }}</small></td>
                    <td>{{ $emprunt->date_emprunt->format('d/m/Y') }}</td>
                    <td>{{ $emprunt->date_retour_effective ? $emprunt->date_retour_effective->format('d/m/Y') : ($emprunt->date_retour_prevue->format('d/m/Y') . ' (Prévu)') }}</td>
                    <td align="center">
                        <span class="status-badge status-{{ str_replace(' ', '-', $emprunt->statut) }}">
                            {{ strtoupper($emprunt->statut) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Fiche d'activité officielle délivrée par le système de gestion BibliotequeUniv
    </div>
</body>
</html>
