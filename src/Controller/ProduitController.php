<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Enum\CategorieProduit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produit')]
final class ProduitController extends AbstractController
{
    private function handleImageUpload($imageFile, Produit $produit): ?string
    {
        if ($imageFile) {
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $extension = $imageFile->guessExtension();
            if (!in_array($extension, $allowedExtensions)) {
                $this->addFlash('error', 'Le fichier doit être une image de type JPG ou PNG.');
                return null;
            }

            if ($imageFile->getSize() > 5 * 1024 * 1024) {
                $this->addFlash('error', 'Le fichier est trop volumineux. La taille maximale autorisée est de 5 Mo.');
                return null;
            }

            $filename = uniqid() . '.' . $extension;
            try {
                $imageFile->move(
                    $this->getParameter('produit_images_directory'),
                    $filename
                );
                return $filename;
            } catch (FileException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                return null;
            }
        }
        return null;
    }

    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository): Response
    {
        // Récupérer la catégorie depuis le paramètre GET
        $categorie = $request->query->get('categorie');

        if ($categorie) {
            // Appliquer le filtrage par catégorie
            $produits = $produitRepository->findByCategorie($categorie);
        } else {
            // Sinon, récupérer tous les produits
            $produits = $produitRepository->findAll();
        }

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }
    
    

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $produit = new Produit();
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Associer l'agriculteur à ce produit
        $user = $this->getUser();
        if ($user && in_array('ROLE_AGRICULTEUR', $user->getRoles())) {
            $produit->setAgriculteur($user); // L'utilisateur connecté devient l'agriculteur du produit
        } else {
            $this->addFlash('error', 'Vous devez être un agriculteur pour ajouter un produit.');
            return $this->redirectToRoute('app_home');
        }

        // Gérer l'image si présente
        $imageFile = $form->get('imageFile')->getData();
        $filename = $this->handleImageUpload($imageFile, $produit);
        if ($filename) {
            $produit->setImage($filename);
        }

        // Sauvegarder le produit
        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Produit ajouté avec succès!');
        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('produit/new.html.twig', [
        'produit' => $produit,
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }


    #[Route('/admin/produits', name: 'admin_produits', methods: ['GET'])]
    public function liste(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();
        return $this->render('admin/produits/index.html.twig', [
            'produits' => $produits,
        ]);
}

#[Route('/admin/produits/{id<\d+>}', name: 'admin_produit_afficher', methods: ['GET'])]
public function afficher(Produit $produit): Response
{
    return $this->render('admin/produits/show.html.twig', [
        'produit' => $produit,
    ]);
}

#[Route('/modifier/{id}', name: 'admin_produit_modifier', methods: ['GET', 'POST'])]
    public function modifierProduit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Produit mis à jour avec succès.');
            return $this->redirectToRoute('admin_produit_afficher', ['id' => $produit->getId()]);
        }

        return $this->render('admin/produits/edit.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }

    #[Route('/admin/produits/supprimer/{id}', name: 'admin_produit_supprimer', methods: ['POST'])]
    public function supprimer(Produit $produit, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
            $this->addFlash('success', 'Produit supprimé avec succès.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }
    
        return $this->redirectToRoute('admin_produits');
    }
    
    
    




    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $filename = $this->handleImageUpload($imageFile, $produit);
                if ($filename) {
                    $produit->setImage($filename);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Produit mis à jour avec succès!');
            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/commande/panier', name: 'produits_panier')]
public function afficherPanier(Request $request, ProduitRepository $produitRepository): Response
{
    $produits = $produitRepository->findAll();
    return $this->render('produit/liste_panier.html.twig', [
        'produits' => $produits,
    ]);
}

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit supprimé avec succès!');
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    
    
}
}
