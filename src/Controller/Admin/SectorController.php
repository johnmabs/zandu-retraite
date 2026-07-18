<?php

namespace App\Controller\Admin;

use App\Entity\AdminUser;
use App\Entity\Sector;
use App\Entity\SubSector;
use App\Enum\AdminPermission;
use App\Enum\AuditEventType;
use App\Form\SectorType;
use App\Form\SubSectorType;
use App\Repository\SectorRepository;
use App\Service\DomainEventRecorder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/secteurs')]
class SectorController extends AbstractController
{
    #[Route('', name: 'admin_sector_list')]
    public function list(SectorRepository $sectorRepository): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::EditSettings);

        return $this->render('admin/sector_list.html.twig', [
            'sectors' => $sectorRepository->findAllWithSubSectors(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_sector_new')]
    public function new(Request $request, EntityManagerInterface $em, DomainEventRecorder $eventRecorder): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::EditSettings);

        $sector = new Sector();
        $form = $this->createForm(SectorType::class, $sector);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($sector);
            $em->flush();

            /** @var AdminUser $admin */
            $admin = $this->getUser();
            $eventRecorder->record(
                eventType: AuditEventType::SettingsUpdated,
                description: sprintf('%s a créé le secteur %s', $admin->getFullName(), $sector->getName()),
                actorAdmin: $admin,
            );

            $this->addFlash('success', 'Secteur créé.');

            return $this->redirectToRoute('admin_sector_list');
        }

        return $this->render('admin/sector_form.html.twig', ['form' => $form, 'title' => 'Nouveau secteur']);
    }

    #[Route('/{id}/modifier', name: 'admin_sector_edit')]
    public function edit(Sector $sector, Request $request, EntityManagerInterface $em, DomainEventRecorder $eventRecorder): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::EditSettings);

        $form = $this->createForm(SectorType::class, $sector);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            /** @var AdminUser $admin */
            $admin = $this->getUser();
            $eventRecorder->record(
                eventType: AuditEventType::SettingsUpdated,
                description: sprintf('%s a modifié le secteur %s', $admin->getFullName(), $sector->getName()),
                actorAdmin: $admin,
            );

            $this->addFlash('success', 'Secteur mis à jour.');

            return $this->redirectToRoute('admin_sector_list');
        }

        return $this->render('admin/sector_form.html.twig', ['form' => $form, 'title' => 'Modifier ' . $sector->getName(), 'sector' => $sector]);
    }

    #[Route('/{id}/supprimer', name: 'admin_sector_delete', methods: ['POST'])]
    public function delete(Sector $sector, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::EditSettings);

        if (!$this->isCsrfTokenValid('sector-delete-' . $sector->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($sector->isOther()) {
            $this->addFlash('error', 'Le secteur "Autre" est un secteur système, il ne peut pas être supprimé.');

            return $this->redirectToRoute('admin_sector_list');
        }

        if (!$sector->getSubSectors()->isEmpty()) {
            $this->addFlash('error', 'Ce secteur a des sous-secteurs, supprimez-les d\'abord.');

            return $this->redirectToRoute('admin_sector_list');
        }

        // Un secteur avec des membres rattachés ne peut pas être supprimé
        // (Member::sector est nullable: false) — Doctrine lèvera une
        // contrainte de clé étrangère de toute façon, mais autant donner un
        // message clair plutôt que laisser remonter une erreur SQL brute.
        $em->remove($sector);

        try {
            $em->flush();
            $this->addFlash('success', 'Secteur supprimé.');
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Des membres sont rattachés à ce secteur, suppression impossible.');
        }

        return $this->redirectToRoute('admin_sector_list');
    }

    #[Route('/{id}/sous-secteurs/nouveau', name: 'admin_subsector_new')]
    public function newSubSector(Sector $sector, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::EditSettings);

        $subSector = new SubSector();
        $subSector->setSector($sector);

        $form = $this->createForm(SubSectorType::class, $subSector);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($subSector);
            $em->flush();

            $this->addFlash('success', 'Sous-secteur ajouté.');

            return $this->redirectToRoute('admin_sector_list');
        }

        return $this->render('admin/subsector_form.html.twig', ['form' => $form, 'sector' => $sector]);
    }

    #[Route('/sous-secteurs/{id}/supprimer', name: 'admin_subsector_delete', methods: ['POST'])]
    public function deleteSubSector(SubSector $subSector, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::EditSettings);

        if (!$this->isCsrfTokenValid('subsector-delete-' . $subSector->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $em->remove($subSector);

        try {
            $em->flush();
            $this->addFlash('success', 'Sous-secteur supprimé.');
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Des membres sont rattachés à ce sous-secteur, suppression impossible.');
        }

        return $this->redirectToRoute('admin_sector_list');
    }
}
