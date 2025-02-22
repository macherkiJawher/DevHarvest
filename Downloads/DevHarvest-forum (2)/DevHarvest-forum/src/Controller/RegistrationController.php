<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Enum\RoleEnum;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher // Injection du service de hachage
    ): Response {
        $user = new User();
        
        // Utilisation du formulaire UserType
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un mot de passe a été saisi avant de le hacher
            if ($user->getPassword()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);
            }
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('registration/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    
}
