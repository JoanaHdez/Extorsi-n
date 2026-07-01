<?php

namespace App\Modules\Extorsion\Constancias\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class ConstanciaPdfService
{
    public function generar(array $registro, string $plantillaPath): array
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $html = view('App\Modules\Extorsion\Constancias\Views\ConstanciaPdf', [
            'registro' => $registro,
            'plantilla' => $this->imageDataUri($plantillaPath, 'image/png'),
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        return [
            'filename' => 'constancia-' . strtolower($registro['folio']) . '.pdf',
            'content' => $dompdf->output(),
        ];
    }

    private function imageDataUri(string $path, string $mime): string
    {
        if (! is_file($path)) {
            return '';
        }

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}