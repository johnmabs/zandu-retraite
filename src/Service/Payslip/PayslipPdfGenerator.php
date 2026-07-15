<?php

namespace App\Service\Payslip;

use App\Entity\Payslip;
use Dompdf\Dompdf;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final class PayslipPdfGenerator
{
    public function __construct(
        private readonly Environment $twig,
        #[Autowire(param: 'payslip_storage_dir')]
        private readonly string $storageDir,
    ) {}

    // Retourne uniquement le nom de fichier (pas le chemin complet), stocké tel quel dans Payslip::pdfPath
    public function generate(Payslip $payslip): string
    {
        $html = $this->twig->render('pdf/payslip.html.twig', ['payslip' => $payslip]);

        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $filename = sprintf('%s.pdf', $payslip->getPayslipNumber());
        $fullPath = $this->storageDir . '/' . $filename;

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0775, true);
        }

        file_put_contents($fullPath, $dompdf->output());

        return $filename;
    }
}
