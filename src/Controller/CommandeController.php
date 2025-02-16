<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;  // Ajout de l'import du repository Produit
use App\Service\PanierService; // Assurez-vous que vous avez ce service
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande')]
final class CommandeController extends AbstractController
{
    #[Route(name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/produits', name: 'app_produit_liste', methods: ['GET'])]
    public function liste(ProduitRepository $produitRepository): Response
    {
        return $this->render('commande/produits.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }


    

    #[Route('/ajouter/{id}', name: 'app_commande_ajouter')]
    public function ajouterCommande(PanierService $panierService, int $id): Response
    {
        // Ajout du produit au panier
        $panierService->ajouterProduit($id);

        // Message flash de succès
        $this->addFlash('success', 'Produit ajouté à votre commande !');

        return $this->redirectToRoute('app_commande_produits');
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF pour supprimer la commande
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/ajouter/{id}', name: 'app_commande_ajouter')]
    public function ajouterAuPanier(PanierService $panierService, int $id): Response
    {
        $panierService->ajouterProduit($id);
        $this->addFlash('success', 'Produit ajouté au panier !');

        return $this->redirectToRoute('app_produit_liste');
    }

    #[Route('/panier', name: 'app_panier')]
    public function afficherPanier(PanierService $panierService): Response
    {
        return $this->render('commande/panier.html.twig', [
            'panier' => $panierService->getPanier(),
        ]);
    }

    #[Route('/panier/supprimer/{id}', name: 'app_commande_supprimer')]
    public function supprimerProduit(PanierService $panierService, int $id): Response
    {
        $panierService->supprimerProduit($id);
        $this->addFlash('success', 'Produit supprimé du panier !');
    
        return $this->redirectToRoute('app_panier');
    }
    
    #[Route('/panier/vider', name: 'app_commande_vider')]
    public function viderPanier(PanierService $panierService): Response
    {
        $panierService->viderPanier();
        $this->addFlash('success', 'Panier vidé !');
    
        return $this->redirectToRoute('app_panier');
    }
    
    
}
