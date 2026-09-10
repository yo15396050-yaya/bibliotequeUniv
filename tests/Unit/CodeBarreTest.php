<?php

namespace Tests\Unit;

use App\Support\CodeBarre;
use PHPUnit\Framework\TestCase;

class CodeBarreTest extends TestCase
{
    public function test_le_svg_genere_est_valide(): void
    {
        $svg = CodeBarre::svg('ALG-0001');

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringEndsWith('</svg>', $svg);
        $this->assertStringContainsString('ALG-0001', $svg);
        $this->assertStringContainsString('<rect', $svg);
    }

    public function test_deux_codes_differents_produisent_des_motifs_differents(): void
    {
        $this->assertNotSame(CodeBarre::svg('ALG-0001'), CodeBarre::svg('ALG-0002'));
    }

    public function test_la_data_uri_est_encodee_en_base64(): void
    {
        $uri = CodeBarre::dataUri('MAT-0042');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);

        $decode = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')), true);
        $this->assertStringContainsString('MAT-0042', $decode);
    }

    public function test_les_caracteres_non_imprimables_sont_ignores(): void
    {
        $svg = CodeBarre::svg("ABC\x00\x01123");

        $this->assertStringContainsString('ABC123', $svg);
    }
}
