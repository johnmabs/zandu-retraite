<?php

namespace App\Controller\Admin;

use App\Entity\AdminUser;
use App\Enum\AdminPermission;
use App\Enum\AuditEventType;
use App\Form\SettingType;
use App\Repository\SettingRepository;
use App\Service\DomainEventRecorder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class SettingController extends AbstractController
{
    #[Route('/admin/parametres', name: 'admin_settings')]
    public function edit(
        Request $request,
        SettingRepository $settingRepository,
        EntityManagerInterface $em,
        DomainEventRecorder $eventRecorder,
    ): Response {
        $this->denyAccessUnlessGranted(AdminPermission::EditSettings);

        $setting = $settingRepository->getOrCreate();
        $thresholds = $setting->getSalaryCategoryThresholds();

        $form = $this->createForm(SettingType::class, $setting);

        // Pré-remplissage des 3 champs non mappés (mapped: false) — la seule
        // méthode valide pour ça, le formulaire n'a pas d'autre moyen de savoir
        // quelle valeur initiale leur donner puisqu'ils ne sont pas liés à
        // l'entité $setting directement.
        $form->get('silverMin')->setData($thresholds['silver']['min'] ?? 2000);
        $form->get('goldMin')->setData($thresholds['gold']['min'] ?? 5000);
        $form->get('platinumMin')->setData($thresholds['platinum']['min'] ?? 10000);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $silverMin = $form->get('silverMin')->getData();
            $goldMin = $form->get('goldMin')->getData();
            $platinumMin = $form->get('platinumMin')->getData();

            if (!($silverMin < $goldMin && $goldMin < $platinumMin)) {
                $this->addFlash('error', 'Les seuils doivent être strictement croissants (Argent < Or < Platine).');

                return $this->render('admin/settings.html.twig', ['form' => $form]);
            }

            $setting->setSalaryCategoryThresholds([
                'bronze' => ['min' => 0, 'max' => $silverMin - 1],
                'silver' => ['min' => $silverMin, 'max' => $goldMin - 1],
                'gold' => ['min' => $goldMin, 'max' => $platinumMin - 1],
                'platinum' => ['min' => $platinumMin, 'max' => null],
            ]);

            $em->flush();

            /** @var AdminUser $admin */
            $admin = $this->getUser();
            $eventRecorder->record(
                eventType: AuditEventType::SettingsUpdated,
                description: sprintf('%s a modifié les paramètres globaux', $admin->getFullName()),
                actorAdmin: $admin,
            );

            $this->addFlash('success', 'Paramètres mis à jour.');

            return $this->redirectToRoute('admin_settings');
        }

        return $this->render('admin/settings.html.twig', ['form' => $form]);
    }
}
