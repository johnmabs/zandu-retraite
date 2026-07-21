<?php

namespace App\Controller\Web\Admin;

use App\Entity\AdminUser;
use App\Entity\Member;
use App\Enum\AdminPermission;
use App\Enum\MemberStatus;
use App\Repository\MemberRepository;
use App\Repository\PaymentRepository;
use App\Repository\SectorRepository;
use App\Service\MemberValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class MemberController extends AbstractController
{
    #[Route('/admin/members', name: 'admin_member_list')]
    public function list(Request $request, MemberRepository $memberRepository, SectorRepository $sectorRepository): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManageMembers);

        $status = $request->query->get('status') ? MemberStatus::from($request->query->get('status')) : null;
        $sector = $request->query->get('sector') ? $sectorRepository->find($request->query->get('sector')) : null;

        $members = $memberRepository->search(
            status: $status,
            sector: $sector,
            searchTerm: $request->query->get('q'),
            page: $request->query->getInt('page', 1),
        );

        return $this->render('admin/member_list.html.twig', [
            'members' => $members,
            'sectors' => $sectorRepository->findAllOrdered(),
            'statuses' => MemberStatus::cases(),
        ]);
    }

    #[Route('/admin/members/pending', name: 'admin_member_pending')]
    public function pending(MemberRepository $memberRepository): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManageRegistrations);

        return $this->render('admin/member_pending.html.twig', [
            'members' => $memberRepository->findPendingRegistrations(),
        ]);
    }

    #[Route('/admin/members/{id}', name: 'admin_member_detail')]
    public function detail(Member $member, PaymentRepository $paymentRepository): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManageMembers);

        return $this->render('admin/member_detail.html.twig', [
            'member' => $member,
            'payments' => $paymentRepository->findByMember($member),
        ]);
    }

    #[Route('/admin/members/{id}/approve', name: 'admin_member_approve', methods: ['POST'])]
    public function approve(Member $member, Request $request, MemberValidationService $validationService): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManageRegistrations);

        if (!$this->isCsrfTokenValid('member-approve-' . $member->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        /** @var AdminUser $admin */
        $admin = $this->getUser();
        $validationService->approve($member, $admin);

        $this->addFlash('success', 'Inscription validée.');

        return $this->redirectToRoute('admin_member_pending');
    }

    #[Route('/admin/members/{id}/reject', name: 'admin_member_reject', methods: ['POST'])]
    public function reject(Member $member, Request $request, MemberValidationService $validationService): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManageRegistrations);

        if (!$this->isCsrfTokenValid('member-reject-' . $member->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        /** @var AdminUser $admin */
        $admin = $this->getUser();
        $validationService->reject($member, $admin, $request->request->get('reason'));

        $this->addFlash('success', 'Inscription rejetée.');

        return $this->redirectToRoute('admin_member_pending');
    }
}
