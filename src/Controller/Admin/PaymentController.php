<?php

namespace App\Controller\Admin;

use App\Entity\AdminUser;
use App\Entity\Member;
use App\Entity\Payment;
use App\Enum\AdminPermission;
use App\Repository\PaymentRepository;
use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class PaymentController extends AbstractController
{
    #[Route('/admin/versements/en-attente', name: 'admin_payment_review_queue')]
    public function reviewQueue(PaymentRepository $paymentRepository): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManagePayments);

        return $this->render('admin/payment_review_queue.html.twig', [
            'payments' => $paymentRepository->findAwaitingManualReview(),
        ]);
    }

    #[Route('/admin/versements/{id}/valider', name: 'admin_payment_approve', methods: ['POST'])]
    public function approve(Payment $payment, Request $request, PaymentService $paymentService): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManagePayments);

        if (!$this->isCsrfTokenValid('payment-approve-' . $payment->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        /** @var AdminUser $admin */
        $admin = $this->getUser();
        $paymentService->reviewManualPayment($payment, $admin, approve: true);

        $this->addFlash('success', 'Versement confirmé.');

        return $this->redirectToRoute('admin_payment_review_queue');
    }

    #[Route('/admin/versements/{id}/rejeter', name: 'admin_payment_reject', methods: ['POST'])]
    public function reject(Payment $payment, Request $request, PaymentService $paymentService): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManagePayments);

        if (!$this->isCsrfTokenValid('payment-reject-' . $payment->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        /** @var AdminUser $admin */
        $admin = $this->getUser();
        $paymentService->reviewManualPayment($payment, $admin, approve: false, note: $request->request->get('note'));

        $this->addFlash('success', 'Versement rejeté.');

        return $this->redirectToRoute('admin_payment_review_queue');
    }

    #[Route('/admin/membres/{id}/versement-especes', name: 'admin_payment_record_cash')]
    public function recordCash(Member $member, Request $request, PaymentService $paymentService): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::ManagePayments);

        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $payment = new Payment();
        $form = $this->createForm(\App\Form\CashPaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paymentService->recordCashByAdmin($member, $payment, $admin);

            $this->addFlash('success', 'Versement en espèces enregistré.');

            return $this->redirectToRoute('admin_member_detail', ['id' => $member->getId()]);
        }

        return $this->render('admin/payment_record_cash.html.twig', ['form' => $form, 'member' => $member]);
    }
}
