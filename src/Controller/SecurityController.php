<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
    
    #[Route('/redirect-by-role', name: 'app_redirect_by_role')]
    public function redirectByRole(): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        $role = $user->getRole(); 
    
        return match ($role) {
            'ROLE_ADMIN' => $this->redirectToRoute('admin_dashboard'),
            'ROLE_AGRICULTEUR' => $this->redirectToRoute('dashboard_agriculteur'),
            'ROLE_FOURNISSEUR' => $this->redirectToRoute('dashboard_fournisseur'),
            'ROLE_CLIENT' => $this->redirectToRoute('dashboard_client'),
            'ROLE_TECHNICIEN' => $this->redirectToRoute('dashboard_technicien'),
            default => $this->redirectToRoute('app_home'),
        };
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
