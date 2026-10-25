<?php

namespace Tests\Feature;

use App\Support\PdfDocumentBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class PdfDocumentBuilderTest extends TestCase
{
    public function test_pdf_options_keep_remote_php_off_and_chroot_inside_the_app(): void
    {
        $pdf = PdfDocumentBuilder::applyOptions(Pdf::loadHTML('<h1>Saf</h1>'));
        $binary = $pdf->output();
        $chroot = $pdf->getDomPDF()->getOptions()->getChroot();
        $chroot = array_map(static fn ($path) => realpath($path) ?: $path, (array) $chroot);

        $this->assertStringStartsWith('%PDF', $binary);
        $this->assertFalse($pdf->getDomPDF()->getOptions()->getIsRemoteEnabled());
        $this->assertFalse($pdf->getDomPDF()->getOptions()->getIsPhpEnabled());
        $this->assertContains(realpath(base_path()), $chroot);
    }
}
