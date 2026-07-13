<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MemberLoginController extends AbstractController
{
    #[Route('/espace-client/login', name: 'member_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si déjà connecté, inutile de revoir le formulaire
        if ($this->getUser()) {
            return $this->redirectToRoute('member_dashboard');
        }

        return $this->render('security/member_login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/espace-client/logout', name: 'member_logout')]
    public function logout(): never
    {
        throw new \LogicException('Intercepté par le firewall avant d\'atteindre ce contrôleur.');
    }
}
