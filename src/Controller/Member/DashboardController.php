<?php

namespace App\Controller\Member;

use App\Entity\Member;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/espace-client', name: 'member_dashboard')]
    #[IsGranted('ROLE_MEMBER')]
    public function index(PaymentRepository $paymentRepository): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $this->render('member/dashboard.html.twig', [
            'member' => $member,
            'totalContributed' => $paymentRepository->sumConfirmedAmountByMember($member),
        ]);
    }
}
