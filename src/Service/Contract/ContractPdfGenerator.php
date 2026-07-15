<?php

namespace App\Service\Contract;

use App\Entity\IssuedContract;
use Dompdf\Dompdf;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final class ContractPdfGenerator
{
    public function __construct(
        private readonly Environment $twig,
        #[Autowire(param: 'contract_storage_dir')]
        private readonly string $storageDir,
    ) {}

    public function generate(IssuedContract $contract): string
    {
        $html = $this->twig->render('pdf/contract.html.twig', ['contract' => $contract]);

        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $filename = sprintf('%s.pdf', $contract->getId());

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0775, true);
        }

        file_put_contents($this->storageDir . '/' . $filename, $dompdf->output());

        return $filename;
    }
}
