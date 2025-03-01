<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\RoleEnum;
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

    private function createUser(
        string $email,
        string $username,
        string $password,
        RoleEnum $role,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $user->setRole($role->value);

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response("Utilisateur '$username' créé avec succès avec le rôle {$role->value}");
    }

    #[Route('/create-agriculteur', name: 'create_agriculteur')]
    public function createAgriculteur(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        return $this->createUser(
            'agriculteur@test.com',
            'AgriculteurUser',
            'password123',
            RoleEnum::AGRICULTEUR,
            $passwordHasher,
            $entityManager
        );
    }

    #[Route('/create-client', name: 'create_client')]
    public function createClient(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        return $this->createUser(
            'client@test.com',
            'ClientUser',
            'password123',
            RoleEnum::CLIENT,
            $passwordHasher,
            $entityManager
        );
    }

    #[Route('/create-fournisseur', name: 'create_fournisseur')]
    public function createFournisseur(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        return $this->createUser(
            'fournisseur@test.com',
            'FournisseurUser',
            'password123',
            RoleEnum::FOURNISSEUR,
            $passwordHasher,
            $entityManager
        );
    }

    #[Route('/create-admin', name: 'create_admin')]
    public function createAdmin(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        return $this->createUser(
            'admin@test.com',
            'AdminUser',
            'password123',
            RoleEnum::ADMIN,
            $passwordHasher,
            $entityManager
        );
    }
}
