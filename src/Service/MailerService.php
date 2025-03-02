<?php

// src/Service/MailerService.php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendConfirmationEmail(string $email, string $commandeId)
    {
        $emailMessage = (new Email())
            ->from('nadarhoumaa@gmail.com')  // Remplace avec l'email de l'expéditeur
            ->to($email)
            ->subject('Confirmation de votre commande')
            ->text("Bonjour, votre commande #{$commandeId} a été confirmée avec succès. Merci de votre achat !");

        try {
            $this->mailer->send($emailMessage);
        } catch (\Exception $e) {
            // Gestion de l'erreur d'envoi d'email (logs, messages d'erreur)
            throw new \Exception("Erreur lors de l'envoi de l'email: " . $e->getMessage());
        }
    }
}
