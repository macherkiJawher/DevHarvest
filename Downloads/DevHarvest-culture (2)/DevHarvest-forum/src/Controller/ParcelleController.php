<?php
namespace App\Controller;

use App\Entity\Parcelle;
use App\Form\ParcelleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;

class ParcelleController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger, PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
        $this->paginator = $paginator;
    }

    #[Route('/parcelle', name: 'parcelle_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Récupère toutes les parcelles
        $queryBuilder = $this->entityManager->getRepository(Parcelle::class)->createQueryBuilder('p');

        // Pagination
        $parcelles = $this->paginator->paginate(
            $queryBuilder, // La requête à paginer
            $request->query->getInt('page', 1), // La page actuelle
            10 // Le nombre de parcelles par page
        );

        return $this->render('parcelle/index.html.twig', [
            'parcelles' => $parcelles,
        ]);
    }

    #[Route('/parcelle/new', name: 'parcelle_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $parcelle = new Parcelle();
        $form = $this->createForm(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form->get('image')->getData(), $parcelle);
            $this->entityManager->persist($parcelle);
            $this->entityManager->flush();

            $this->addFlash('success', 'Parcelle ajoutée avec succès !');
            return $this->redirectToRoute('parcelle_index');
        }

        return $this->render('parcelle/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/parcelle/{id}', name: 'parcelle_show', methods: ['GET'])]
    public function show(Parcelle $parcelle): Response
    {
        return $this->render('parcelle/show.html.twig', ['parcelle' => $parcelle]);
    }

    #[Route('/parcelle/{id}/edit', name: 'parcelle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Parcelle $parcelle): Response
    {
        $form = $this->createForm(ParcelleType::class, $parcelle);
        $form->handleRequest($request);
        $oldImage = $parcelle->getImage();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form->get('image')->getData(), $parcelle, $oldImage);
            $this->entityManager->flush();

            $this->addFlash('success', 'Parcelle modifiée avec succès !');
            return $this->redirectToRoute('parcelle_index');
        }

        return $this->render('parcelle/edit.html.twig', [
            'form' => $form->createView(),
            'parcelle' => $parcelle
        ]);
    }

    #[Route('/parcelle/{id}/delete', name: 'parcelle_delete', methods: ['POST'])]
    public function delete(Request $request, Parcelle $parcelle): Response
    {
        if ($this->isCsrfTokenValid('delete' . $parcelle->getId(), $request->request->get('_token'))) {
            if ($parcelle->getImage()) {
                $imagePath = $this->getParameter('parcelle_images_directory') . '/' . $parcelle->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $this->entityManager->remove($parcelle);
            $this->entityManager->flush();

            $this->addFlash('success', 'Parcelle supprimée avec succès.');
        }

        return $this->redirectToRoute('parcelle_index');
    }

    // Génération du PDF
    #[Route('/{id}/pdf', name: 'parcelle_pdf')]
    public function generatePdf(Parcelle $parcelle): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('parcelle/pdf.html.twig', [
            'parcelle' => $parcelle,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="parcelle_'.$parcelle->getId().'.pdf"',
        ]);
    }

    private function handleImageUpload(?UploadedFile $imageFile, Parcelle $parcelle, ?string $oldImage = null): void
    {
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move($this->getParameter('parcelle_images_directory'), $newFilename);
                $parcelle->setImage($newFilename);

                if ($oldImage) {
                    $oldImagePath = $this->getParameter('parcelle_images_directory') . '/' . $oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
            }
        } elseif ($oldImage) {
            $parcelle->setImage($oldImage);
        }
    }
}
