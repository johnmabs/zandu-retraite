<?php

namespace App\Controller\Web\Member;

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
    #[Route('/member-area/payslips', name: 'member_payslip_list')]
    public function list(PayslipRepository $payslipRepository): \Symfony\Component\HttpFoundation\Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $this->render('member/payslip_list.html.twig', [
            'payslips' => $payslipRepository->findByMember($member),
        ]);
    }

    #[Route('/member-area/payslips/{id}/download', name: 'member_payslip_download')]
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
