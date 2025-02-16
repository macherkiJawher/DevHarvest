<?php
namespace App\Service;

use App\Entity\Produit;

class PanierService
{
    private $panier = [];

    public function ajouterAuPanier(Produit $produit)
    {
        $this->panier[] = $produit;
    }

    public function getPanier()
    {
        return $this->panier;
    }

    public function viderPanier()
    {
        $this->panier = [];
    }
}
