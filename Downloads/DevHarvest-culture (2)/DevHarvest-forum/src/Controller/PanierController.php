<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\PanierItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProduitRepository;

class PanierController extends AbstractController
{
    /**
     * @Route("/panier", name="app_panier")
     */
    public function index(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        // Récupérer les produits du panier dans la session
        $panier = $session->get('panier', []);
        $produitsPanier = [];

        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $produitsPanier[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                ];
            }
        }

        // Liste complète des produits
        $produits = $produitRepository->findAll();

        return $this->render('panier/index.html.twig', [
            'produits' => $produits, // Liste complète des produits
            'produitsPanier' => $produitsPanier, // Produits dans le panier
        ]);
    }

    /**
     * @Route("/panier/ajouter/{id}", name="ajouter_au_panier")
     */
    public function ajouterAuPanier(Produit $produit, SessionInterface $session, EntityManagerInterface $em): Response
    {
        // Récupération du panier en session
        $panier = $session->get('panier', []);

        // Si le produit est déjà dans le panier, on incrémente la quantité
        if (isset($panier[$produit->getId()])) {
            $panier[$produit->getId()]++;
        } else {
            // Sinon, on l'ajoute avec une quantité de 1
            $panier[$produit->getId()] = 1;
        }

        // Enregistrer dans la session
        $session->set('panier', $panier);

        // Enregistrer également dans la base de données avec l'entité Panier
        $panierEntity = new Panier();
        $panierEntity->setProduit($produit);
        $panierEntity->setQuantite($panier[$produit->getId()]);

        $em->persist($panierEntity);
        $em->flush();

        // Ajouter un message flash
        $this->addFlash('success', 'Produit ajouté au panier !');

        // Redirection vers la page du panier
        return $this->redirectToRoute('app_panier');
    }

    /**
     * @Route("/panier/vider", name="vider_panier")
     */
    public function viderPanier(SessionInterface $session): Response
    {
        // Vider le panier en session
        $session->remove('panier');

        // Ajouter un message flash
        $this->addFlash('success', 'Le panier a été vidé.');

        return $this->redirectToRoute('app_panier');
    }
}
