<?php

namespace App\Controller\Member;

use App\Entity\IssuedContract;
use App\Entity\Member;
use App\Repository\IssuedContractRepository;
use App\Service\Contract\ContractGenerationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEMBER')]
class ContractController extends AbstractController
{
    #[Route('/espace-client/contrat', name: 'member_contract_list')]
    public function list(IssuedContractRepository $repository): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $this->render('member/contract_list.html.twig', [
            'contracts' => $repository->findByMember($member),
        ]);
    }

    #[Route('/espace-client/contrat/{id}/telecharger', name: 'member_contract_download')]
    public function download(IssuedContract $contract, #[Autowire(param: 'contract_storage_dir')] string $storageDir): BinaryFileResponse
    {
        $this->denyAccessIfNotOwner($contract);

        $response = new BinaryFileResponse($storageDir . '/' . $contract->getPdfPath());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'contrat-' . $contract->getId() . '.pdf');

        return $response;
    }

    #[Route('/espace-client/contrat/{id}/signer', name: 'member_contract_sign', methods: ['POST'])]
    #[IsCsrfTokenValid(new Expression('"contract-sign-" ~ args["contract"].id'))]
    public function sign(IssuedContract $contract, ContractGenerationService $service): Response
    {
        $this->denyAccessIfNotOwner($contract);

        try {
            $service->sign($contract);
            $this->addFlash('success', 'Contrat signé.');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('member_contract_list');
    }

    private function denyAccessIfNotOwner(IssuedContract $contract): void
    {
        /** @var Member $member */
        $member = $this->getUser();

        if ($contract->getMember() !== $member) {
            throw $this->createAccessDeniedException();
        }
    }
}
