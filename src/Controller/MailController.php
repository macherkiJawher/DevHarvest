<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    #[Route('/test-mail', name: 'test_mail')]
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('rhouma.eya@etudiant-fst.utm.tn')
            ->to('eyarhouma25@gmail.com')
            ->subject('Test Symfony Mailer')
            ->text('Ceci est un test Symfony Mailer.');

        $mailer->send($email);

        return new Response('E-mail envoyé avec succès !');
    }
}
