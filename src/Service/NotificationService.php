<?php

namespace App\Service;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createNotification(int $userId, ?int $eventId, string $message): Notification
    {
        $notification = new Notification();
        $notification->setUserId($userId);
        $notification->setEventId($eventId);
        $notification->setMessage($message);
        $notification->setIsRead(false);
        $notification->setCreatedAt(new \DateTime());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->setIsRead(true);
        $this->entityManager->flush();
    }

    public function getUnreadNotifications(int $userId): array
    {
        return $this->entityManager->getRepository(Notification::class)
            ->findBy([
                'user_id' => $userId,
                'is_read' => false
            ], ['created_at' => 'DESC']);
    }
} 