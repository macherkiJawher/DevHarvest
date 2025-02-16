<?php
// src/Controller/ParcelleController.php

namespace App\Controller;

use App\Entity\Parcelle;
use App\Form\ParcelleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ParcelleController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Route pour afficher la liste des parcelles
    #[Route('/parcelle', name: 'parcelle_index')]
    public function index(): Response
    {
        $parcelles = $this->entityManager->getRepository(Parcelle::class)->findAll();

        return $this->render('parcelle/index.html.twig', [
            'parcelles' => $parcelles,
        ]);
    }

    // Route pour afficher les détails d'une parcelle
    #[Route('/parcelle/{id}', name: 'parcelle_show')]
    public function show(Parcelle $parcelle): Response
    {
        return $this->render('parcelle/show.html.twig', [
            'parcelle' => $parcelle,
        ]);
    }

    // Route pour créer une nouvelle parcelle
    #[Route('/parcelle/new', name: 'parcelle_new')]
    public function new(Request $request): Response
    {
        $parcelle = new Parcelle();
        $form = $this->createForm(ParcelleType::class, $parcelle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('parcelle_images_directory'),  // Répertoire défini dans services.yaml
                        $newFilename
                    );
                    $parcelle->setImage($newFilename);  // Associer l'image à la parcelle
                } catch (FileException $e) {
                    // Gérer l'exception en cas d'erreur lors du déplacement du fichier
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image.');
                    return $this->render('parcelle/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            // Persist et flush les données dans la base
            $this->entityManager->persist($parcelle);
            $this->entityManager->flush();

            // Message flash de confirmation
            $this->addFlash('success', 'La parcelle a été ajoutée avec succès !');

            // Rediriger vers la liste des parcelles après la création
            return $this->redirectToRoute('parcelle_index');
        }

        return $this->render('parcelle/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour modifier une parcelle existante
    #[Route('/parcelle/{id}/edit', name: 'parcelle_edit')]
    public function edit(Request $request, Parcelle $parcelle): Response
    {
        $form = $this->createForm(ParcelleType::class, $parcelle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image si une nouvelle image est téléchargée
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('parcelle_images_directory'),  // Répertoire défini dans services.yaml
                        $newFilename
                    );
                    $parcelle->setImage($newFilename);  // Mettre à jour l'image de la parcelle
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image.');
                    return $this->render('parcelle/edit.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Les informations de la parcelle ont été mises à jour.');

            return $this->redirectToRoute('parcelle_index');
        }

        return $this->render('parcelle/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour supprimer une parcelle
    #[Route('/parcelle/{id}/delete', name: 'parcelle_delete')]
    public function delete(Parcelle $parcelle): Response
    {
        $this->entityManager->remove($parcelle);
        $this->entityManager->flush();

        $this->addFlash('success', 'La parcelle a été supprimée avec succès.');

        return $this->redirectToRoute('parcelle_index');
    }
}

