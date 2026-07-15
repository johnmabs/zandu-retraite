<?php

namespace App\Controller\Admin;

use App\Entity\AdminUser;
use App\Entity\Member;
use App\Repository\ContractTemplateRepository;
use App\Service\Contract\ContractGenerationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ContractController extends AbstractController
{
    #[Route('/admin/membres/{id}/contrat/emettre', name: 'admin_contract_issue', methods: ['POST'])]
    #[IsCsrfTokenValid(new Expression('"contract-issue-" ~ args["member"].id'))]
    public function issue(Member $member, ContractTemplateRepository $templateRepository, ContractGenerationService $service): Response
    {
        $this->denyAccessUnlessGranted(\App\Enum\AdminPermission::ManageMembers);

        $template = $templateRepository->findActive();
        if (!$template) {
            $this->addFlash('error', 'Aucun modèle de contrat actif.');

            return $this->redirectToRoute('admin_member_detail', ['id' => $member->getId()]);
        }

        /** @var AdminUser $admin */
        $admin = $this->getUser();
        $contract = $service->issueForMember($member, $template, $admin);

        $this->addFlash('success', 'Contrat émis.');

        return $this->redirectToRoute('admin_member_detail', ['id' => $member->getId()]);
    }
}
