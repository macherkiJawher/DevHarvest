<?php

// src/Controller/DashboardController.php
namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(ProduitRepository $produitRepository)
    {
        // Récupérer tous les produits
        $produits = $produitRepository->findAll();

        // Calcul du total des produits et du stock total
        $totalProduits = count($produits);
        $totalStock = array_sum(array_map(fn($p) => $p->getQuantitestock(), $produits));

        // Déterminer les produits en rupture de stock
        $produitsEnRupture = count(array_filter($produits, fn($p) => $p->getQuantitestock() == 0));

        // Préparer les données pour le graphique
        $categories = [];
        $stocks = [];

        foreach ($produits as $produit) {
            $categories[] = $produit->getNom();
            $stocks[] = $produit->getQuantitestock();
        }

        return $this->render('dashboard/index.html.twig', [
            'produits' => $produits,  
            'totalProduits' => $totalProduits,
            'totalStock' => $totalStock,
            'produitsEnRupture' => $produitsEnRupture,  // ✅ Ajout de cette variable
            'categories' => json_encode($categories),
            'stocks' => json_encode($stocks),
        ]);
    }
}
