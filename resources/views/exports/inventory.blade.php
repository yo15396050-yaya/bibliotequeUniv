<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventaire Bibliothèque</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #D4AF37; padding-bottom: 15px; margin-bottom: 30px; }
        .header h1 { color: #5D4037; margin: 0; text-transform: uppercase; font-size: 22px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 12px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #5D4037; color: #fff; text-align: left; padding: 10px; font-size: 11px; text-transform: uppercase; }
        td { border-bottom: 1px solid #eee; padding: 10px; font-size: 10px; vertical-align: top; }
        tr:nth-child(even) { background-color: #fafafa; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .label-stock { color: #5D4037; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventaire de la Bibliothèque Universitaire</h1>
        <p>Généré le : {{ $date }} | État actuel des ouvrages</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">ISBN</th>
                <th width="35%">Titre du Livre</th>
                <th width="20%">Auteur</th>
                <th width="20%">Catégorie</th>
                <th width="13%">Rayon / Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($livres as $livre)
                <tr>
                    <td>{{ $livre->isbn }}</td>
                    <td><strong>{{ $livre->titre }}</strong></td>
                    <td>{{ $livre->auteur }}</td>
                    <td>{{ $livre->categorie }}</td>
                    <td align="center">
                        <span class="label-stock">{{ $livre->emplacement_rayon }}</span><br>
                        ({{ $livre->exemplaires_disponibles }}/{{ $livre->exemplaires_totaux }})
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Document officiel - Page 1
    </div>
</body>
</html>
