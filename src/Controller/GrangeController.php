<?php

namespace App\Controller;

use App\Entity\Grange;
use App\Form\GrangeType;
use App\Repository\GrangeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;



#[Route('/grange')]
final class GrangeController extends AbstractController
{
    private $slugger;

   
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }


    #[Route(name: 'app_grange_index', methods: ['GET'])]
    public function index(GrangeRepository $grangeRepository): Response
    {
        return $this->render('grange/index.html.twig', [
            'granges' => $grangeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_grange_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $grange = new Grange();
        $form = $this->createForm(GrangeType::class, $grange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('zones_directory'), 
                        $newFilename
                    );
                    $grange->setImage($newFilename);
                } catch (FileException $e) {
                    
                }
            }
            $entityManager->persist($grange);
            $entityManager->flush();

            return $this->redirectToRoute('app_grange_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grange/new.html.twig', [
            'grange' => $grange,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grange_show', methods: ['GET'])]
    public function show(Grange $grange): Response
    {
        return $this->render('grange/show.html.twig', [
            'grange' => $grange,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_grange_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Grange $grange, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GrangeType::class, $grange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_grange_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grange/edit.html.twig', [
            'grange' => $grange,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grange_delete', methods: ['POST'])]
    public function delete(Request $request, Grange $grange, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$grange->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($grange);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_grange_index', [], Response::HTTP_SEE_OTHER);
    }
    
}

