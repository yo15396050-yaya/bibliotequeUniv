<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Retards & Amendes</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #E74C3C; padding-bottom: 15px; margin-bottom: 30px; }
        .header h1 { color: #123A7A; font-size: 22px; text-transform: uppercase; margin: 0; }
        .header p { color: #E74C3C; font-weight: bold; margin: 5px 0 0; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #123A7A; color: #fff; padding: 10px; font-size: 11px; text-transform: uppercase; border: none; }
        td { border-bottom: 1px solid #eee; padding: 10px; font-size: 10px; color: #444; }
        .text-bold { font-weight: bold; }
        .text-danger { color: #E74C3C; font-weight: bold; }
        
        .total-section { margin-top: 30px; text-align: right; background-color: #fcf8f2; padding: 20px; border: 1px solid #eaddca; border-radius: 8px; }
        .total-amount { font-size: 18px; color: #E74C3C; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport des Retards & Amendes dûes</h1>
        <p>Situation au {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="25%" align="left">Étudiant</th>
                <th width="35%" align="left">Livre</th>
                <th width="15%">Prévu le</th>
                <th width="15%">Jours retard</th>
                <th width="10%" align="right">Amende</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retards as $retard)
                @php 
                    $jours = now()->startOfDay()->diffInDays($retard->date_retour_prevue->startOfDay(), false);
                @endphp
                <tr>
                    <td>
                        <span class="text-bold">{{ $retard->user->name }}</span><br>
                        <small>{{ $retard->user->matricule }}</small>
                    </td>
                    <td>
                        <span class="text-bold">{{ $retard->livre->titre }}</span><br>
                        <small>ISBN: {{ $retard->livre->isbn }}</small>
                    </td>
                    <td align="center">{{ $retard->date_retour_prevue->format('d/m/Y') }}</td>
                    <td align="center" class="text-danger">{{ abs($jours) }} Jours</td>
                    <td align="right" class="text-danger">{{ number_format($retard->montant_amende, 0, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <span class="text-bold">Total des amendes cumulées :</span><br>
        <div class="total-amount">{{ number_format($totalAmendes, 0, ',', ' ') }} FCFA</div>
    </div>

    <div class="footer">
        Ceci est un document de suivi interne - Page 1
    </div>
</body>
</html>
