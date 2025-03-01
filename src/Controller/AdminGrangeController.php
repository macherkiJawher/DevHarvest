<?php

namespace App\Controller;

use App\Entity\Grange;
use App\Form\GrangeType;
use App\Repository\GrangeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/grange')]
class AdminGrangeController extends AbstractController
{
    #[Route('/', name: 'admin_grange_index', methods: ['GET'])]
    public function index(GrangeRepository $grangeRepository): Response
    {
        return $this->render('admin_grange/index.html.twig', [
            'granges' => $grangeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_grange_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $grange = new grange();
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

            return $this->redirectToRoute('admin_grange_index');
        }

        return $this->render('admin_grange/new.html.twig', [
            'grange' => $grange,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}', name: 'admin_grange_show', methods: ['GET'])]
    public function show(Grange $grange): Response
    {
        return $this->render('admin_grange/show.html.twig', [
            'grange' => $grange,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_grange_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Grange $grange, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GrangeType::class, $grange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_grange_index');
        }

        return $this->render('admin_grange/edit.html.twig', [
            'grange' => $grange,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_grange_delete', methods: ['POST'])]
    public function delete(Request $request, Grange $grange, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $grange->getId(), $request->request->get('_token'))) {
            $entityManager->remove($grange);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_grange_index');
    }

    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
}

