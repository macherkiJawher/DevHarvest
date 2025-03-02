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
use App\Service\PaginationService;
use App\Service\RendementService;
use Dompdf\Dompdf;
use Dompdf\Options;

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
        $rendement = $this->rendementService->calculerRendement($culture);
        return $this->render('culture/rendement.html.twig', [
            'culture' => $culture,
            'rendement' => $rendement,
        ]);
    }

    #[Route('/', name: 'app_culture_index', methods: ['GET'])]
    public function index(Request $request, CultureRepository $cultureRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 9;
        $cultures = $cultureRepository->findBy([], [], $limit, ($page - 1) * $limit);
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

    #[Route('/new', name: 'culture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $culture = new Culture();
        $form = $this->createForm(CultureType::class, $culture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']; // Extensions autorisées
                $extension = $image->guessExtension();

                if (!in_array($extension, $allowedExtensions)) {
                    $this->addFlash('error', 'Seules les images JPG, JPEG, PNG et GIF sont autorisées.');
                    return $this->redirectToRoute('culture_new');
                }

                $newFileName = uniqid() . '.' . $extension;
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
            $image = $form->get('image')->getData();
            if ($image) {
                if ($culture->getImage()) {
                    $oldImagePath = $this->getParameter('images_directory') . '/' . $culture->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']; // Extensions autorisées
                $extension = $image->guessExtension();

                if (!in_array($extension, $allowedExtensions)) {
                    $this->addFlash('error', 'Seules les images JPG, JPEG, PNG et GIF sont autorisées.');
                    return $this->redirectToRoute('culture_edit', ['id' => $culture->getId()]);
                }

                $newFileName = uniqid() . '.' . $extension;
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
            if ($culture->getImage()) {
                $oldImagePath = $this->getParameter('images_directory') . '/' . $culture->getImage();
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $entityManager->remove($culture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_culture_index');
    }

    // Génération du PDF
    #[Route('/{id}/pdf', name: 'culture_pdf')]
    public function generatePdf(Culture $culture): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('culture/pdf.html.twig', [
            'culture' => $culture,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="culture_'.$culture->getId().'.pdf"',
        ]);
    }
}
