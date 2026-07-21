<?php

namespace App\Controller\Web\Member;

use App\Entity\IssuedContract;
use App\Entity\Member;
use App\Repository\IssuedContractRepository;
use App\Service\Contract\ContractGenerationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEMBER')]
class ContractController extends AbstractController
{
    #[Route('/member-area/contract', name: 'member_contract_list')]
    public function list(IssuedContractRepository $repository): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        return $this->render('member/contract_list.html.twig', [
            'contracts' => $repository->findByMember($member),
        ]);
    }

    #[Route('/member-area/contract/{id}/download', name: 'member_contract_download')]
    public function download(IssuedContract $contract, #[Autowire(param: 'contract_storage_dir')] string $storageDir): BinaryFileResponse
    {
        $this->denyAccessIfNotOwner($contract);

        $response = new BinaryFileResponse($storageDir . '/' . $contract->getPdfPath());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'contrat-' . $contract->getId() . '.pdf');

        return $response;
    }

    #[Route('/member-area/contrat/{id}/sign', name: 'member_contract_sign', methods: ['POST'])]
    public function sign(IssuedContract $contract, Request $request, ContractGenerationService $service): Response
    {
        $this->denyAccessIfNotOwner($contract);

        if (!$this->isCsrfTokenValid('contract-sign-' . $contract->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

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
