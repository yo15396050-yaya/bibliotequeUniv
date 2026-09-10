<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Étiquette — {{ $exemplaire->code_barre }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 24px; background: #f4f4f4; }
        .etiquette {
            width: 320px; background: #fff; border: 1px solid #ccc; border-radius: 8px;
            padding: 14px; margin: 0 auto 16px; text-align: center;
        }
        .etiquette h1 { font-size: 13px; margin: 0 0 2px; }
        .etiquette .auteur { font-size: 11px; color: #555; margin-bottom: 8px; }
        .etiquette .cote { font-size: 12px; font-weight: bold; letter-spacing: 1px; margin-top: 6px; }
        .etiquette img { max-width: 100%; }
        .visuels { display: flex; align-items: center; justify-content: center; gap: 10px; }
        .actions { text-align: center; }
        .actions button { padding: 8px 18px; cursor: pointer; }
        @media print { body { background: #fff; padding: 0; } .actions { display: none; } }
    </style>
</head>
<body>
    <div class="etiquette">
        <h1>{{ $exemplaire->livre?->titre }}</h1>
        <div class="auteur">{{ $exemplaire->livre?->auteur }}</div>
        <div class="visuels">
            <img src="{{ $codeBarre }}" alt="Code-barres {{ $exemplaire->code_barre }}" style="height:70px;">
            <img src="{{ $qrCode }}" alt="QR Code" style="height:80px;">
        </div>
        <div class="cote">{{ $exemplaire->emplacement?->cote ?? $exemplaire->livre?->emplacement_rayon }}</div>
    </div>

    <div class="actions">
        <button onclick="window.print()">Imprimer l'étiquette</button>
    </div>
</body>
</html>
