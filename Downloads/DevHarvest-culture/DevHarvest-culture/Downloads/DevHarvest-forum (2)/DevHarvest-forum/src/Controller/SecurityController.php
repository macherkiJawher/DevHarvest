<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, le rediriger vers son tableau de bord
        if ($this->getUser()) {
            return $this->redirectToRoute('app_redirect_by_role');
        }

        // Récupération de la dernière erreur d'authentification et du dernier username saisi
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/redirect-by-role', name: 'app_redirect_by_role')]
    public function redirectByRole(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            return $this->redirectToRoute('admin_dashboard');
        } elseif (in_array('ROLE_AGRICULTEUR', $roles)) {
            return $this->redirectToRoute('dashboard_agriculteur');
        } elseif (in_array('ROLE_FOURNISSEUR', $roles)) {
            return $this->redirectToRoute('dashboard_fournisseur');
        } elseif (in_array('ROLE_CLIENT', $roles)) {
            return $this->redirectToRoute('dashboard_client');
        } elseif (in_array('ROLE_TECHNICIEN', $roles)) {
            return $this->redirectToRoute('dashboard_technicien');
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode sera interceptée par Symfony, ne pas ajouter de logique ici.
    }

    #[Route(path: '/signup', name: 'app_signup')]
    public function signup(): Response
    {
        return $this->render('security/signup.html.twig');
    }
}
