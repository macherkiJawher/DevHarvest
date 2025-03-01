<?php
// src/Controller/CultureController.php

namespace App\Controller;

use App\Entity\Culture;
use App\Form\CultureType;
use App\Repository\CultureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PaginationService;
use App\Service\RendementService;

#[Route('/culture')]
class CultureController extends AbstractController
{
    private RendementService $rendementService;
    private PaginationService $paginationService;

    public function __construct(RendementService $rendementService, PaginationService $paginationService)
    {
        $this->rendementService = $rendementService;
        $this->paginationService = $paginationService;
    }

    // Affichage du rendement de la culture
    #[Route('/{id}/rendement', name: 'culture_rendement')]
    public function afficherRendement(Culture $culture): Response
    {
        // Appel du service pour calculer le rendement
        $rendement = $this->rendementService->calculerRendement($culture); 
        return $this->render('culture/rendement.html.twig', [
            'culture' => $culture,
            'rendement' => $rendement,
        ]);
    }

    #[Route('/', name: 'app_culture_index', methods: ['GET'])]
    public function index(Request $request, CultureRepository $cultureRepository): Response
    {
        // Vérification de la page et de la récupération des cultures
        $page = $request->query->getInt('page', 1);
        $limit = 9;  // Nombre d'éléments par page
        $query = $cultureRepository->createQueryBuilder('c')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();
    
        $cultures = $query->getResult();
    
        // Vérification du contenu de cultures
        dump($cultures); // Ajoutez cette ligne pour vérifier si des cultures sont récupérées
    
        // Total des cultures
        $totalCultures = $cultureRepository->count([]);
        $totalPages = ceil($totalCultures / $limit);
    
        return $this->render('culture/index.html.twig', [
            'cultures' => $cultures,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
            ]
        ]);
    }
    
    // Création d'une nouvelle culture
    #[Route('/new', name: 'culture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $culture = new Culture();
        $form = $this->createForm(CultureType::class, $culture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image si elle existe
            $image = $form->get('image')->getData();
            if ($image) {
                $newFileName = uniqid() . '.' . $image->guessExtension();
                $image->move($this->getParameter('images_directory'), $newFileName);
                $culture->setImage($newFileName);
            }

            // Sauvegarde de la culture dans la base de données
            $entityManager->persist($culture);
            $entityManager->flush();

            return $this->redirectToRoute('app_culture_index');
        }

        return $this->render('culture/new.html.twig', [
            'culture' => $culture,
            'form' => $form->createView(),
        ]);
    }

    // Affichage des détails d'une culture
    #[Route('/{id}', name: 'culture_show', methods: ['GET'])]
    public function show(Culture $culture): Response
    {
        return $this->render('culture/show.html.twig', [
            'culture' => $culture,
        ]);
    }

    // Modification d'une culture existante
    #[Route('/{id}/edit', name: 'culture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Culture $culture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CultureType::class, $culture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $culture->setDatePlantation($form->get('datePlantation')->getData());
            $culture->setDateRecolte($form->get('dateRecolte')->getData());

            // Gestion de l'image si elle existe
            $image = $form->get('image')->getData();
            if ($image) {
                if ($culture->getImage()) {
                    $oldImagePath = $this->getParameter('images_directory') . '/' . $culture->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $newFileName = uniqid() . '.' . $image->guessExtension();
                $image->move($this->getParameter('images_directory'), $newFileName);
                $culture->setImage($newFileName);
            }

            // Sauvegarde des changements dans la base de données
            $entityManager->flush();

            $this->addFlash('success', 'La culture a été mise à jour avec succès.');
            return $this->redirectToRoute('app_culture_index');
        }

        return $this->render('culture/edit.html.twig', [
            'culture' => $culture,
            'form' => $form->createView(),
        ]);
    }

    // Suppression d'une culture
    #[Route('/{id}', name: 'culture_delete', methods: ['POST'])]
    public function delete(Request $request, Culture $culture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $culture->getId(), $request->request->get('_token'))) {
            $entityManager->remove($culture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_culture_index');
    }
}
