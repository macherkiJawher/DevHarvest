<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/machine')]
class MachineController extends AbstractController
{
    private $csrfTokenManager;
    private $slugger;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager, SluggerInterface $slugger)
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->slugger = $slugger;
    }

    /**
     * Liste des machines.
     */
    #[Route('/', name: 'app_machine_index', methods: ['GET'])]
    public function index(MachineRepository $machineRepository): Response
    {
        $machines = $machineRepository->findAll();
        $csrfTokens = [];
        foreach ($machines as $machine) {
            $csrfTokens[$machine->getId()] = $this->csrfTokenManager->getToken('delete' . $machine->getId())->getValue();
        }
        return $this->render('machine/index.html.twig', [
            'machines' => $machines,
            'csrf_tokens' => $csrfTokens,
        ]);
    }
    #[Route('/list', name: 'app_machine_list', methods: ['GET'])]
    public function list(MachineRepository $machineRepository): Response
    {
        $machines = $machineRepository->findAll();
        $csrfTokens = [];
        foreach ($machines as $machine) {
            $csrfTokens[$machine->getId()] = $this->csrfTokenManager->getToken('delete' . $machine->getId())->getValue();
        }
        return $this->render('machine/list.html.twig', [
            'machines' => $machines,
            'csrf_tokens' => $csrfTokens,
        ]);
    }

    /**
     * Créer une nouvelle machine.
     */
    #[Route('/new', name: 'app_machine_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $machine = new Machine();
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Paramètre défini dans services.yaml
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer les erreurs d'upload
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }

                // Stocker le nom du fichier dans l'entité
                $machine->setImage($newFilename);
            }

            $entityManager->persist($machine);
            $entityManager->flush();

            $this->addFlash('success', 'La machine a été créée avec succès.');
            return $this->redirectToRoute('app_machine_index');
        }

        return $this->renderForm('machine/new.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    /**
     * Afficher les détails d'une machine.
     */
    #[Route('/{id}', name: 'app_machine_show', methods: ['GET'])]
    public function show(Machine $machine): Response
    {
        return $this->render('machine/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    /**
     * Modifier une machine existante.
     */
    #[Route('/{id}/edit', name: 'app_machine_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Machine $machine, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Paramètre défini dans services.yaml
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer les erreurs d'upload
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }

                // Stocker le nom du fichier dans l'entité
                $machine->setImage($newFilename);
            }

            $entityManager->flush();

            $this->addFlash('success', 'La machine a été mise à jour avec succès.');
            return $this->redirectToRoute('app_machine_index');
        }

        return $this->renderForm('machine/edit.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    /**
     * Supprimer une machine.
     */
    #[Route('/{id}', name: 'app_machine_delete', methods: ['POST'])]
    public function delete(Request $request, Machine $machine, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $machine->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée si elle existe
            $imagePath = $this->getParameter('images_directory') . '/' . $machine->getImage();
            if ($machine->getImage() && file_exists($imagePath)) {
                unlink($imagePath);
            }

            $entityManager->remove($machine);
            $entityManager->flush();

            $this->addFlash('success', 'La machine a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_machine_index');
    }
}