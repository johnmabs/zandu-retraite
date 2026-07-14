<?php

namespace App\Controller\Member;

use App\Entity\Member;
use App\Form\RegistrationType;
use App\Service\MemberRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/espace-client/inscription', name: 'member_registration')]
    public function register(Request $request, MemberRegistrationService $registrationService): Response
    {
        $member = new Member();
        $form = $this->createForm(RegistrationType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationService->register($member, $form->get('pin')->getData());

            $this->addFlash('success', sprintf(
                'Inscription enregistrée sous le numéro %s. Elle sera examinée par un administrateur.',
                $member->getMemberNumber(),
            ));

            return $this->redirectToRoute('member_registration_success', ['memberNumber' => $member->getMemberNumber()]);
        }

        // Le composant Live rend son propre formulaire ; pas besoin de transmettre $form ici
        return $this->render('member/registration.html.twig');
    }

    #[Route('/espace-client/inscription/succes/{memberNumber}', name: 'member_registration_success')]
    public function success(string $memberNumber): Response
    {
        return $this->render('member/registration_success.html.twig', [
            'memberNumber' => $memberNumber,
        ]);
    }
}
