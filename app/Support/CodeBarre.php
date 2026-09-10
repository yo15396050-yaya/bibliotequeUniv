<?php

namespace App\Support;

/**
 * Générateur de code-barres Code 128 (jeu B) au format SVG.
 *
 * Implémenté sans dépendance externe : les étiquettes d'exemplaires doivent
 * pouvoir être imprimées même sur une installation hors ligne.
 */
class CodeBarre
{
    /** Largeurs des barres/espaces pour les 107 symboles du Code 128. */
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '211133',
    ];

    private const START_B = 104;

    private const STOP = 106;

    /**
     * Rend le code sous forme de SVG.
     *
     * @param  int  $largeurModule  largeur (px) de la barre unitaire
     * @param  int  $hauteur  hauteur (px) des barres
     */
    public static function svg(string $code, int $largeurModule = 2, int $hauteur = 60, bool $avecTexte = true): string
    {
        $code = preg_replace('/[^\x20-\x7E]/', '', $code) ?: '0';
        $symboles = self::encoder($code);

        $largeurTotale = 0;
        foreach ($symboles as $symbole) {
            foreach (str_split(self::PATTERNS[$symbole]) as $largeur) {
                $largeurTotale += (int) $largeur;
            }
        }
        $largeurTotale *= $largeurModule;

        $hauteurTotale = $hauteur + ($avecTexte ? 18 : 0);
        $barres = '';
        $x = 0;
        foreach ($symboles as $symbole) {
            $barre = true; // les motifs alternent barre / espace
            foreach (str_split(self::PATTERNS[$symbole]) as $largeur) {
                $l = (int) $largeur * $largeurModule;
                if ($barre) {
                    $barres .= sprintf('<rect x="%d" y="0" width="%d" height="%d" fill="#000"/>', $x, $l, $hauteur);
                }
                $x += $l;
                $barre = ! $barre;
            }
        }

        $texte = $avecTexte ? sprintf(
            '<text x="%d" y="%d" font-family="monospace" font-size="13" text-anchor="middle" fill="#000">%s</text>',
            (int) ($largeurTotale / 2),
            $hauteur + 15,
            htmlspecialchars($code, ENT_QUOTES)
        ) : '';

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">'
            .'<rect width="100%%" height="100%%" fill="#fff"/>%s%s</svg>',
            $largeurTotale, $hauteurTotale, $largeurTotale, $hauteurTotale, $barres, $texte
        );
    }

    /** SVG encodé en data URI, directement utilisable dans un <img src>. */
    public static function dataUri(string $code, int $largeurModule = 2, int $hauteur = 60): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode(self::svg($code, $largeurModule, $hauteur));
    }

    /**
     * @return array<int, int> suite des symboles (start, données, checksum, stop)
     */
    private static function encoder(string $code): array
    {
        $symboles = [self::START_B];
        $somme = self::START_B;

        foreach (str_split($code) as $position => $caractere) {
            $valeur = ord($caractere) - 32;
            $symboles[] = $valeur;
            $somme += $valeur * ($position + 1);
        }

        $symboles[] = $somme % 103; // clé de contrôle
        $symboles[] = self::STOP;

        return $symboles;
    }
}
