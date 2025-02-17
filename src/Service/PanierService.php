<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class PanierService
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function ajouterProduit(int $produitId)
    {
        $request = $this->requestStack->getCurrentRequest();
        $panier = $request->cookies->get('panier', '[]'); // Valeur par défaut comme tableau vide en JSON
        $panier = json_decode($panier, true);

        // Ajout du produit au panier
        $panier[] = $produitId;

        // On met à jour le cookie avec le nouveau panier
        setcookie('panier', json_encode($panier), time() + 3600, '/');
    }

    public function getPanier()
    {
        $request = $this->requestStack->getCurrentRequest();
        $panier = $request->cookies->get('panier', '[]'); // Valeur par défaut comme tableau vide en JSON
        return json_decode($panier, true);
    }

    public function supprimerProduit(int $produitId)
    {
        $request = $this->requestStack->getCurrentRequest();
        $panier = $request->cookies->get('panier', '[]'); // Valeur par défaut comme tableau vide en JSON
        $panier = json_decode($panier, true);

        // Retirer le produit du panier
        $panier = array_diff($panier, [$produitId]);

        setcookie('panier', json_encode($panier), time() + 3600, '/');
    }

    public function viderPanier()
    {
        // Supprimer le cookie du panier
        setcookie('panier', '', time() - 3600, '/');
    }

    // Calculer le total du panier
    public function getTotal()
    {
        $request = $this->requestStack->getCurrentRequest();
        $panier = $request->cookies->get('panier', '[]'); // Valeur par défaut comme tableau vide en JSON
        $panier = json_decode($panier, true);

        $total = 0;
        // Ici tu devrais récupérer les prix des produits dans ton panier
        foreach ($panier as $produitId) {
            // Exemple de récupération du prix d'un produit à partir de l'ID
            // Tu dois remplacer ceci par un appel à ton repository pour obtenir les détails du produit
            $prixProduit = 10; // Exemple : Remplace ceci par la logique pour récupérer le prix réel du produit
            $total += $prixProduit;
        }

        return $total;
    }
}
