<?php
// src/Controller/CommandeController.php

namespace App\Controller;

use App\Service\FactureService;
use App\Service\MailerService;
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
use App\Form\CoordonneesType;

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

    #[Route('/commander', name: 'app_commande_commander')]
    public function commander(PanierService $panierService, EntityManagerInterface $entityManager): Response
    {
        $panier = $panierService->getPanier();
        if (empty($panier)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier');
        }

        $commande = new Commande();
        $commande->setDateCommande(new \DateTime());
        $commande->setEtat(EtatCommande::VALIDEE);

        $total = 0;
        foreach ($panier as $produitId) {
            $produit = $entityManager->getRepository(Produit::class)->find($produitId);
            if ($produit && $produit->getQuantiteStock() > 0) {
                $total += $produit->getPrixUnitaire();
                $produit->setQuantiteStock($produit->getQuantiteStock() - 1);
                $entityManager->persist($produit);
            } else {
                $this->addFlash('error', 'Le produit "' . $produit->getNom() . '" est en rupture de stock.');
                return $this->redirectToRoute('app_panier');
            }
        }

        $commande->setTotal($total);
        $entityManager->persist($commande);
        $entityManager->flush();

        $panierService->viderPanier();

        $this->addFlash('success', 'Votre commande a été passée avec succès.');
        return $this->redirectToRoute('commande_confirmation', ['id' => $commande->getId()]);
    }

    #[Route('/commande/confirmation/{id}', name: 'commande_confirmation')]
    public function confirmation(Request $request, CommandeRepository $commandeRepository, int $id): Response
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        $form = $this->createForm(CoordonneesType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire (par exemple, l'email)
            $data = $form->getData();
            $email = $data['email'];

            // Ajouter une notification pour la confirmation
            $this->addFlash('success', 'Votre commande a bien été confirmée !');

            // Rediriger vers la page de succès avec l'email comme paramètre
            return $this->redirectToRoute('payment_success', [
                'email' => $email,
                'commandeId' => $commande->getId(),
            ]);
        }

        return $this->render('commande/confirmation.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande,
        ]);
    }

    #[Route('/panier/supprimer/{id}', name: 'app_commande_supprimer')]
    public function supprimer(PanierService $panierService, int $id): Response
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

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();
            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/commandes', name: 'admin_commandes')]
    public function commandes(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();

        return $this->render('admin/commandes/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commandes/{id}', name: 'admin_commande_show')]
    public function afficher(int $id, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('La commande n\'existe pas');
        }

        return $this->render('admin/commandes/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/payment/success', name: 'payment_success')]
    public function paymentSuccess(MailerService $mailerService, Request $request, CommandeRepository $commandeRepository): Response
    {
        $email = $request->query->get('email');
        $commandeId = $request->query->get('commandeId');

        if (!$email || !$commandeId) {
            $this->addFlash('error', 'Des informations sont manquantes pour confirmer le paiement.');
            return $this->redirectToRoute('app_commande_index');
        }

        $commande = $commandeRepository->find($commandeId);

        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        // Envoi du mail de confirmation
        $mailerService->sendConfirmationEmail($email, $commandeId);

        return $this->render('payment/success.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/facture/{id}', name: 'generate_pdf')]
    public function generatePdf($id, FactureService $factureService, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->find($id);
    
        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable');
        }
    
        $pdfContent = $factureService->generateInvoice($commande);
    
        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="facture_' . $commande->getId() . '.pdf"',
            ]
        );
    }
    


    
}
