<?php

// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use App\Enum\RoleEnum;  // N'oubliez pas de l'importer
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/create-agriculteur', name: 'create_agriculteur')]
    public function createAgriculteur(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setEmail('agriculteur@test.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles([RoleEnum::ROLE_AGRICULTEUR]);

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('Agriculteur créé avec succès');
    }

    #[Route('/create-client', name: 'create_client')]
    public function createClient(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setEmail('client@test.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles([RoleEnum::ROLE_CLIENT]);

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('Client créé avec succès');
    }

    #[Route('/create-fournisseur', name: 'create_fournisseur')]
    public function createFournisseur(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setEmail('fournisseur@test.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles([RoleEnum::ROLE_FOURNISSEUR]);

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('Fournisseur créé avec succès');
    }

    #[Route('/create-admin', name: 'create_admin')]
    public function createAdmin(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles([RoleEnum::ROLE_ADMIN]);

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('Administrateur créé avec succès');
    }
}
