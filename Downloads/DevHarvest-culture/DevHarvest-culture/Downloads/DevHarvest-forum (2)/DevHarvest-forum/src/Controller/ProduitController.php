<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produit')]
final class ProduitController extends AbstractController
{
    // Méthode privée pour gérer le téléchargement des images
    private function handleImageUpload($imageFile, Produit $produit): ?string
    {
        if ($imageFile) {
            // Validation de l'extension du fichier
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $extension = $imageFile->guessExtension();
            if (!in_array($extension, $allowedExtensions)) {
                $this->addFlash('error', 'Le fichier doit être une image de type JPG ou PNG.');
                return null;
            }

            // Validation de la taille du fichier (max 5 Mo)
            if ($imageFile->getSize() > 5 * 1024 * 1024) {
                $this->addFlash('error', 'Le fichier est trop volumineux. La taille maximale autorisée est de 5 Mo.');
                return null;
            }

            // Créer le nom unique et déplacer le fichier
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
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            $filename = $this->handleImageUpload($imageFile, $produit);
            if ($filename) {
                $produit->setImage($filename);
            }

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

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image si elle est modifiée
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
    public function afficherProduitsPourPanier(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        return $this->render('produit/liste_panier.html.twig', [
            'produits' => $produits,
        ]);}
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
