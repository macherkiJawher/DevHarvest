<?php
// src/Controller/CommandeController.php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\EtatCommande;

#[Route('/commande')]
final class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
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
    public function ajouter(PanierService $panierService, int $id): Response
    {
        $panierService->ajouterProduit($id);
        $this->addFlash('success', 'Produit ajouté au panier !');
        return $this->redirectToRoute('app_produit_liste');
    }

    #[Route('/panier', name: 'app_panier')]
    public function panier(PanierService $panierService, ProduitRepository $produitRepository): Response
    {
        $panier = $panierService->getPanier();
        $total = $panierService->getTotal();
        
        $produitsDetails = [];
        foreach ($panier as $produitId) {
            $produit = $produitRepository->find($produitId);
            if ($produit) {
                $produitsDetails[] = $produit;
            }
        }

        return $this->render('commande/panier.html.twig', [
            'panier' => $produitsDetails,
            'total' => $total,
        ]);
    }

    // Finaliser la commande
    #[Route('/commander', name: 'app_commande_commander')]
    public function commander(PanierService $panierService, EntityManagerInterface $entityManager): Response
    {
        $panier = $panierService->getPanier();
        // Si le panier est vide
        if (empty($panier)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier');
        }

        $commande = new Commande();
        $commande->setDateCommande(new \DateTime()); // Utilisation de setDateCommande()
        $commande->setEtat(EtatCommande::VALIDEE);

        // Calculer le total de la commande
        $total = 0;
        foreach ($panier as $produitId) {
            $produit = $entityManager->getRepository(Produit::class)->find($produitId); // Utilisation de l'EntityManager pour récupérer les produits
            if ($produit) {
                $total += $produit->getPrixUnitaire(); // Assurez-vous que getPrixUnitaire() existe dans votre entité Produit
            }
        }
        $commande->setTotal($total);

        // Enregistrer la commande dans la base de données
        $entityManager->persist($commande);
        $entityManager->flush();

        // Vider le panier après la commande
        $panierService->viderPanier();
        $entityManager->flush();

        // Message de confirmation et redirection
        $this->addFlash('success', 'Votre commande a été passée avec succès.');
        return $this->redirectToRoute('app_commande_confirmation', ['id' => $commande->getId()]);
    }

    // Confirmation de commande
    #[Route('/{id}/confirmation', name: 'app_commande_confirmation')]
    public function confirmation($id, EntityManagerInterface $entityManager): Response
    {
        $commande = $entityManager->getRepository(Commande::class)->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('commande/confirmee.html.twig', [
            'commande' => $commande,
        ]);
    }

    // Supprimer un produit du panier
    #[Route('/panier/supprimer/{id}', name: 'app_commande_supprimer')]
    public function supprimer(PanierService $panierService, int $id): Response
    {
        $panierService->supprimerProduit($id);
        $this->addFlash('success', 'Produit supprimé du panier !');
        return $this->redirectToRoute('app_panier');
    }

    // Vider le panier
    #[Route('/panier/vider', name: 'app_commande_vider')]
    public function viderPanier(PanierService $panierService): Response
    {
        $panierService->viderPanier();
        $this->addFlash('success', 'Panier vidé !');
        return $this->redirectToRoute('app_panier');
    }

    // Ajouter une nouvelle commande
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

    // Afficher une commande
    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    // Modifier une commande
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

    // Supprimer une commande
    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
