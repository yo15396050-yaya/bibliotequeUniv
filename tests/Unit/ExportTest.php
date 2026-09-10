<?php

namespace Tests\Unit;

use App\Support\Export;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ExportTest extends TestCase
{
    private function lignes(): Collection
    {
        return collect([
            ['titre' => 'Algorithmique', 'auteur' => 'Konan', 'emprunts' => 12],
            ['titre' => 'Réseaux ; avancés', 'auteur' => 'Diallo', 'emprunts' => 4],
        ]);
    }

    private function colonnes(): array
    {
        return ['titre' => 'Titre', 'auteur' => 'Auteur', 'emprunts' => 'Emprunts'];
    }

    public function test_l_export_csv_contient_le_bom_et_les_donnees(): void
    {
        $reponse = Export::csv($this->lignes(), $this->colonnes(), 'rapport');

        ob_start();
        $reponse->sendContent();
        $contenu = ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $contenu, 'Le BOM UTF-8 doit précéder le contenu.');
        $this->assertStringContainsString('Titre;Auteur;Emprunts', $contenu);
        $this->assertStringContainsString('Algorithmique', $contenu);
        // Le point-virgule interne est échappé par les guillemets.
        $this->assertStringContainsString('"Réseaux ; avancés"', $contenu);
    }

    public function test_l_export_excel_produit_un_classeur_lisible(): void
    {
        $reponse = Export::excel($this->lignes(), $this->colonnes(), 'rapport', 'Emprunts');
        $contenu = $reponse->getContent();

        $this->assertStringContainsString('<?mso-application progid="Excel.Sheet"?>', $contenu);
        $this->assertStringContainsString('ss:Name="Emprunts"', $contenu);
        $this->assertStringContainsString('Algorithmique', $contenu);
        $this->assertSame(
            'attachment; filename="rapport.xls"',
            $reponse->headers->get('content-disposition')
        );
    }

    public function test_le_xml_est_echappe(): void
    {
        $lignes = collect([['titre' => 'A & B <script>', 'auteur' => '', 'emprunts' => 0]]);

        $contenu = Export::excel($lignes, $this->colonnes(), 'test')->getContent();

        $this->assertStringNotContainsString('<script>', $contenu);
        $this->assertStringContainsString('&amp;', $contenu);
    }
}
