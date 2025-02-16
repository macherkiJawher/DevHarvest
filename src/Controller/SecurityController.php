<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        // Logique de la page de connexion
        return $this->render('login_signup/login.html.twig');
    }

    /**
     * @Route("/signup", name="app_signup")
     */
    #[Route('/signup', name: 'app_signup')]
    public function signup(): Response
    {
        // Logique de la page d'inscription
        return $this->render('login_signup/signup.html.twig');
    }
}
