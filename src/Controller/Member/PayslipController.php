<?php

namespace App\Controller\Member;

use App\Entity\Member;
use App\Entity\Payslip;
use App\Repository\PayslipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEMBER')]
class PayslipController extends AbstractController
{
    #[Route('/espace-client/bulletins', name: 'member_payslip_list')]
    public function list(PayslipRepository $payslipRepository): \Symfony\Component\HttpFoundation\Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $this->render('member/payslip_list.html.twig', [
            'payslips' => $payslipRepository->findByMember($member),
        ]);
    }

    #[Route('/espace-client/bulletins/{id}/telecharger', name: 'member_payslip_download')]
    public function download(
        Payslip $payslip,
        #[Autowire(param: 'payslip_storage_dir')] string $storageDir,
    ): BinaryFileResponse {
        /** @var Member $member */
        $member = $this->getUser();

        // Un membre ne peut télécharger que ses propres bulletins
        if ($payslip->getMember() !== $member) {
            throw $this->createAccessDeniedException();
        }

        $response = new BinaryFileResponse($storageDir . '/' . $payslip->getPdfPath());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $payslip->getPayslipNumber() . '.pdf');

        return $response;
    }
}
