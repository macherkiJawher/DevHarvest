<?php

namespace App\Controller;

use App\Entity\Culture;
use App\Form\CultureType;
use App\Repository\CultureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/culture')]
class CultureController extends AbstractController
{
    #[Route('/', name: 'app_culture_index', methods: ['GET'])]
    public function index(CultureRepository $cultureRepository): Response
    {
        return $this->render('culture/index.html.twig', [
            'cultures' => $cultureRepository->findAll(),
        ]);
    }


    #[Route('/new', name: 'culture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $culture = new Culture();
        $form = $this->createForm(CultureType::class, $culture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle the uploaded image if exists
            $image = $form->get('image')->getData();
            if ($image) {
                $newFileName = uniqid() . '.' . $image->guessExtension();
                $image->move($this->getParameter('images_directory'), $newFileName);
                $culture->setImage($newFileName);
            }

            $entityManager->persist($culture);
            $entityManager->flush();

            return $this->redirectToRoute('app_culture_index');
        }

        return $this->render('culture/new.html.twig', [
            'culture' => $culture,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'culture_show', methods: ['GET'])]
    public function show(Culture $culture): Response
    {
        return $this->render('culture/show.html.twig', [
            'culture' => $culture,
        ]);
    }

    #[Route('/{id}/edit', name: 'culture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Culture $culture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CultureType::class, $culture);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $culture->setDatePlantation($form->get('datePlantation')->getData());
            $culture->setDateRecolte($form->get('dateRecolte')->getData());
    
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
    
            $entityManager->flush();
    
            $this->addFlash('success', 'La culture a été mise à jour avec succès.');
            return $this->redirectToRoute('app_culture_index');
        }
    
        return $this->render('culture/edit.html.twig', [
            'culture' => $culture,
            'form' => $form->createView(),
        ]);
    }
    


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
