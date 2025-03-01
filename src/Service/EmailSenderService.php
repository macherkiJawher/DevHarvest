<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailSenderService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $to, string $subject, string $body)
    {
        $email = (new Email())
            ->from('ton-adresse-email@domaine.com')
            ->to($to)
            ->subject($subject)
            ->text($body); // Utilise .html() pour du contenu HTML

        // Envoi de l'email
        $this->mailer->send($email);
    }
}
