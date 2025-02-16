<?php
// src/Service/PanierService.php
namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierService
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function ajouterProduit(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (!isset($panier[$id])) {
            $panier[$id] = 1;
        } else {
            $panier[$id]++;
        }

        $this->session->set('panier', $panier);
    }

    public function getPanier(): array
    {
        return $this->session->get('panier', []);
    }

    public function supprimerProduit(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (isset($panier[$id])) {
            unset($panier[$id]);
        }

        $this->session->set('panier', $panier);
    }

    public function viderPanier()
    {
        $this->session->remove('panier');
    }
}
