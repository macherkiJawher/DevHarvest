<?php

namespace App\Service;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class NotificationManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Crée une nouvelle notification
     */
    public function creerNotification(string $type, string $message): void
    {
        $notification = new Notification();
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setCreatedAt(new \DateTime());
        $notification->setIsRead(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
