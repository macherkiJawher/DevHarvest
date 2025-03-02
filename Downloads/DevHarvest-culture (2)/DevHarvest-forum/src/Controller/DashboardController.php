<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/agriculteur', name: 'dashboard_agriculteur')]
    public function agriculteur(): Response
    {
        return $this->render('dashboard/agriculteur.html.twig');
    }

    #[Route('/fournisseur', name: 'dashboard_fournisseur')]
    public function fournisseur(): Response
    {
        return $this->render('dashboard/fournisseur.html.twig');
    }

    #[Route('/client', name: 'dashboard_client')]
    public function client(): Response
    {
        return $this->render('dashboard/client.html.twig');
    }

    #[Route('/technicien', name: 'dashboard_technicien')]
    public function technicien(): Response
    {
        return $this->render('dashboard/technicien.html.twig');
    }
}
