<?php

namespace App\Controller\Admin;

use App\Entity\AdminUser;
use App\Entity\Member;
use App\Enum\AdminPermission;
use App\Repository\MemberRepository;
use App\Repository\SectorRepository;
use App\Service\Payslip\PayslipGenerationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class PayslipController extends AbstractController
{
    #[Route('/admin/membres/{id}/bulletin/generer', name: 'admin_payslip_generate', methods: ['POST'])]
    public function generate(Member $member, Request $request, PayslipGenerationService $service): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManagePayslips);

        if (!$this->isCsrfTokenValid('payslip-generate-' . $member->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        /** @var AdminUser $admin */
        $admin = $this->getUser();

        try {
            $payslip = $service->generateForMember($member, $admin);
            $this->addFlash('success', sprintf('Bulletin %s généré.', $payslip->getPayslipNumber()));
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_member_detail', ['id' => $member->getId()]);
    }

    #[Route('/admin/bulletins/generer-lot', name: 'admin_payslip_generate_batch')]
    public function generateBatch(
        Request $request,
        MemberRepository $memberRepository,
        SectorRepository $sectorRepository,
        PayslipGenerationService $service,
    ): Response {
        $this->denyAccessUnlessGranted(AdminPermission::ManagePayslips);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('payslip-generate-batch', $request->request->get('_csrf_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            /** @var AdminUser $admin */
            $admin = $this->getUser();

            $sectorId = $request->request->get('sector');
            $members = $sectorId
                ? $memberRepository->findActiveBySector($sectorRepository->find($sectorId))
                : $memberRepository->findAllActive();

            $result = $service->generateBatch($members, $admin);

            $this->addFlash('success', sprintf('%d bulletin(s) généré(s).', \count($result->succeeded)));
            if ($result->failed) {
                $this->addFlash('warning', sprintf('%d échec(s) : %s', \count($result->failed), implode(', ', array_keys($result->failed))));
            }

            return $this->redirectToRoute('admin_payslip_generate_batch');
        }

        return $this->render('admin/payslip_generate_batch.html.twig', [
            'sectors' => $sectorRepository->findAllOrdered(),
        ]);
    }
}
