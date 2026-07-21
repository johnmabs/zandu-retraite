<?php

namespace App\Controller\Web\Admin;

use App\Entity\AdminUser;
use App\Entity\Notification;
use App\Enum\AdminPermission;
use App\Enum\AuditEventType;
use App\Repository\AuditLogRepository;
use App\Repository\NotificationRepository;
use App\Security\AuditVisibilityResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AlarmController extends AbstractController
{
    #[Route('/admin/alarms', name: 'admin_alarms')]
    public function index(
        Request $request,
        NotificationRepository $notificationRepository,
        AuditLogRepository $auditLogRepository,
        AuditVisibilityResolver $visibilityResolver,
    ): Response {
        $this->denyAccessUnlessGranted(AdminPermission::GlobalView);

        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $eventTypeFilter = $request->query->get('event_type')
            ? AuditEventType::from($request->query->get('event_type'))
            : null;

        return $this->render('admin/alarms.html.twig', [
            'notifications' => $notificationRepository->findRecent(),
            'auditLogs' => $auditLogRepository->search(
                viewerRole: $admin->getRole(),
                eventType: $eventTypeFilter,
                page: $request->query->getInt('page', 1),
            ),
            'visibleEventTypes' => $visibilityResolver->visibleTypesFor($admin->getRole()),
        ]);
    }

    #[Route('/admin/notifications/{id}/read', name: 'admin_notification_read', methods: ['POST'])]
    public function markRead(Notification $notification, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(AdminPermission::GlobalView);

        if (!$this->isCsrfTokenValid('notification-read-' . $notification->getId(), $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $notification->setIsRead(true);
        $em->flush();

        return $this->redirectToRoute('admin_alarms');
    }
}
