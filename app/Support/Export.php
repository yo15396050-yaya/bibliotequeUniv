<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exports tabulaires sans dépendance externe.
 *
 * - CSV : UTF-8 avec BOM et séparateur « ; » (ouverture directe dans Excel FR).
 * - Excel : classeur XML SpreadsheetML, lu nativement par Excel et LibreOffice.
 */
class Export
{
    /**
     * @param  Collection<int, array<string, mixed>>  $lignes
     * @param  array<string, string>  $colonnes  clé => en-tête
     */
    public static function csv(Collection $lignes, array $colonnes, string $nomFichier): StreamedResponse
    {
        return response()->streamDownload(function () use ($lignes, $colonnes) {
            $sortie = fopen('php://output', 'wb');
            fwrite($sortie, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($sortie, array_values($colonnes), ';');

            foreach ($lignes as $ligne) {
                fputcsv($sortie, array_map(
                    fn ($cle) => (string) ($ligne[$cle] ?? ''),
                    array_keys($colonnes)
                ), ';');
            }

            fclose($sortie);
        }, $nomFichier.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $lignes
     * @param  array<string, string>  $colonnes
     */
    public static function excel(Collection $lignes, array $colonnes, string $nomFichier, string $titreFeuille = 'Rapport'): Response
    {
        $echapper = fn ($valeur) => htmlspecialchars((string) $valeur, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<?mso-application progid="Excel.Sheet"?>'."\n"
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            .'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .'<Styles><Style ss:ID="entete"><Font ss:Bold="1"/>'
            .'<Interior ss:Color="#D4AF37" ss:Pattern="Solid"/></Style></Styles>'
            .'<Worksheet ss:Name="'.$echapper(mb_substr($titreFeuille, 0, 31)).'"><Table>';

        $xml .= '<Row>';
        foreach ($colonnes as $entete) {
            $xml .= '<Cell ss:StyleID="entete"><Data ss:Type="String">'.$echapper($entete).'</Data></Cell>';
        }
        $xml .= '</Row>';

        foreach ($lignes as $ligne) {
            $xml .= '<Row>';
            foreach (array_keys($colonnes) as $cle) {
                $valeur = $ligne[$cle] ?? '';
                $type = is_numeric($valeur) && ! is_string($valeur) ? 'Number' : 'String';
                $xml .= '<Cell><Data ss:Type="'.$type.'">'.$echapper($valeur).'</Data></Cell>';
            }
            $xml .= '</Row>';
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nomFichier.'.xls"',
        ]);
    }
}
