<?php

// src/Controller/PanierController.php
namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\AjoutPanierType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class PanierController extends AbstractController
{
    /**
     * @Route("/panier", name="panier_index")
     */
    public function index(EntityManagerInterface $em): Response
    {
        $produits = $em->getRepository(Produit::class)->findAll(); // Liste de tous les produits

        return $this->render('panier/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    /**
     * @Route("/ajouter-au-panier/{id}", name="ajouter_au_panier")
     */
    public function ajouterAuPanier(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        $panier = new Panier();
        $panier->setProduit($produit);

        $form = $this->createForm(AjoutPanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder le produit dans le panier (avec la quantité)
            $em->persist($panier);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté au panier !');
            return $this->redirectToRoute('panier_index');
        }

        return $this->render('panier/ajouter.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }
}
