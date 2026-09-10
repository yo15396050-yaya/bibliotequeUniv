<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche d'Emprunt #{{ $emprunt->id }}</title>
    <style>
        @page { margin: 2cm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        /* En-tête officiel */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #123A7A;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .institution-name {
            font-size: 18px;
            font-weight: bold;
            color: #123A7A;
            text-transform: uppercase;
        }
        .document-type {
            font-size: 14px;
            color: #2563EB;
            font-weight: bold;
        }

        /* Grille d'information */
        .info-grid {
            width: 100%;
            margin-bottom: 25px;
        }
        .info-grid th {
            text-align: left;
            background-color: #f9f6f1;
            padding: 8px;
            width: 30%;
            border: 0.5pt solid #eee;
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
        }
        .info-grid td {
            padding: 8px;
            border: 0.5pt solid #eee;
            width: 70%;
        }

        .section-title {
            background-color: #123A7A;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        /* Status Badges pour PDF */
        .status {
            font-weight: bold;
            text-transform: uppercase;
            padding: 2px 5px;
            border: 1px solid #333;
        }

        /* Signatures */
        .signature-row {
            margin-top: 40px;
            width: 100%;
        }
        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 60px;
            border-bottom: 1px solid #ccc;
            margin-bottom: 5px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="institution-name">Bibliothèque Universitaire</div>
        <div class="document-type">Récépissé d'Emprunt n° {{ str_pad($emprunt->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="section-title">Informations sur le Prêt</div>
    <table class="info-grid">
        <tr>
            <th>Date d'emprunt</th>
            <td>{{ $emprunt->date_emprunt->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Échéance de retour</th>
            <td style="color: #d32f2f; font-weight: bold;">{{ $emprunt->date_retour_prevue->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Statut du document</th>
            <td><span class="status">{{ strtoupper($emprunt->statut) }}</span></td>
        </tr>
    </table>

    <div class="section-title">Détails de l'Étudiant</div>
    <table class="info-grid">
        <tr>
            <th>Nom & Prénoms</th>
            <td>{{ $emprunt->user->name }}</td>
        </tr>
        <tr>
            <th>Matricule / ID</th>
            <td>{{ $emprunt->user->matricule }}</td>
        </tr>
        <tr>
            <th>Filière / Niveau</th>
            <td>{{ $emprunt->user->filiere }}</td>
        </tr>
    </table>

    <div class="section-title">Ouvrage Emprunté</div>
    <table class="info-grid">
        <tr>
            <th>Titre</th>
            <td style="font-weight: bold;">{{ $emprunt->livre->titre }}</td>
        </tr>
        <tr>
            <th>Auteur(s)</th>
            <td>{{ $emprunt->livre->auteur }}</td>
        </tr>
        <tr>
            <th>Code ISBN / Localisation</th>
            <td>{{ $emprunt->livre->isbn }} | Rayon : {{ $emprunt->livre->emplacement_rayon }}</td>
        </tr>
    </table>

    @if($emprunt->notes)
    <div style="margin-top: 15px; font-style: italic;">
        <strong>Note de l'agent :</strong> {{ $emprunt->notes }}
    </div>
    @endif

    <div style="margin-top: 30px; border: 1px solid #eee; padding: 10px; background-color: #fafafa;">
        <strong style="font-size: 10px;">RAPPEL DU RÈGLEMENT :</strong>
        <ul style="font-size: 9px; margin-top: 5px;">
            <li>Tout retard entraînera une amende de 100 FCFA par jour ouvré.</li>
            <li>L'emprunteur est responsable de l'état physique du livre (ne pas surligner, ne pas corner).</li>
            <li>En cas de perte, le remboursement se fera au prix du marché majoré de 20% de frais de dossier.</li>
        </ul>
    </div>

    <div class="signature-row">
        <div class="signature-box">
            <div class="signature-space"></div>
            <strong>Signature de l'Étudiant</strong>
        </div>
        <div class="signature-box" style="float: right;">
            <div class="signature-space"></div>
            <strong>Cachet de la Bibliothèque</strong>
        </div>
    </div>

    <div class="footer">
        Université de Côte d'Ivoire - Service de Documentation<br>
        Généré le {{ now()->format('d/m/Y \à H:i') }} - ID Système : {{ $emprunt->id }}
    </div>
</body>
</html>