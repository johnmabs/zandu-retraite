<?php

namespace App\Controller\Member;

use App\Entity\Member;
use App\Entity\Payment;
use App\Form\PaymentType;
use App\Repository\PaymentRepository;
use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEMBER')]
class PaymentController extends AbstractController
{
    #[Route('/espace-client/versement', name: 'member_payment_declare')]
    public function declare(Request $request, PaymentService $paymentService): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paymentService->declareByMember($member, $payment);

            $this->addFlash('success', 'Votre versement a été déclaré et sera confirmé sous peu.');

            return $this->redirectToRoute('member_payment_history');
        }

        return $this->render('member/payment_declare.html.twig', ['form' => $form]);
    }

    #[Route('/espace-client/historique', name: 'member_payment_history')]
    public function history(Request $request, PaymentRepository $paymentRepository): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $payments = $paymentRepository->findByMember($member, $request->query->getInt('page', 1));

        return $this->render('member/payment_history.html.twig', ['payments' => $payments]);
    }
}
