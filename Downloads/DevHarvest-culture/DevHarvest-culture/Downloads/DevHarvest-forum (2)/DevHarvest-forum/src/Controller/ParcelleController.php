<?php
namespace App\Controller;

use App\Entity\Parcelle;
use App\Form\ParcelleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ParcelleController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    #[Route('/parcelle', name: 'parcelle_index', methods: ['GET'])]
    public function index(): Response
    {
        // Récupère toutes les parcelles dans la base de données
        $parcelles = $this->entityManager->getRepository(Parcelle::class)->findAll();

        // Retourne la vue avec la liste des parcelles
        return $this->render('parcelle/index.html.twig', [
            'parcelles' => $parcelles,
        ]);
    }

    #[Route('/parcelle/new', name: 'parcelle_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Crée une nouvelle instance de la parcelle
        $parcelle = new Parcelle();

        // Crée un formulaire pour ajouter une nouvelle parcelle
        $form = $this->createForm(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        // Si le formulaire est soumis
        if ($form->isSubmitted()) {
            // Si le formulaire est valide
            if ($form->isValid()) {
                // Gérer l'upload de l'image
                $this->handleImageUpload($form->get('image')->getData(), $parcelle, false);

                // Persister la nouvelle parcelle dans la base de données
                $this->entityManager->persist($parcelle);
                $this->entityManager->flush();

                // Ajouter un message flash pour indiquer le succès
                $this->addFlash('success', 'La parcelle a été ajoutée avec succès !');

                // Rediriger vers la liste des parcelles
                return $this->redirectToRoute('parcelle_index');
            } else {
                // Ajouter un message d'erreur si le formulaire n'est pas valide
                $this->addFlash('error', 'Des erreurs se sont produites dans le formulaire.');
            }
        }

        // Retourne la vue du formulaire
        return $this->render('parcelle/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/parcelle/{id}', name: 'parcelle_show', methods: ['GET'])]
    public function show(Parcelle $parcelle): Response
    {
        // Retourne la vue avec les détails de la parcelle
        return $this->render('parcelle/show.html.twig', [
            'parcelle' => $parcelle,
        ]);
    }

    #[Route('/parcelle/{id}/edit', name: 'parcelle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Parcelle $parcelle): Response
    {
        // Vérification si l'utilisateur connecté est autorisé à éditer la parcelle
        

        // Créer l'URL du formulaire d'édition
        $actionUrl = $this->generateUrl('parcelle_edit', ['id' => $parcelle->getId()]);
        
        // Créer le formulaire pour modifier la parcelle
        $form = $this->createForm(ParcelleType::class, $parcelle, ['action' => $actionUrl]);
        
        // Récupérer l'image actuelle si elle existe
        $oldFileName = $parcelle->getImage();
        $oldFileNamePath = $this->getParameter('parcelle_images_directory') . '/' . $oldFileName;

        if ($oldFileName) {
            $pictureFile = new \Symfony\Component\HttpFoundation\File\File($oldFileNamePath);
            $parcelle->setImage($pictureFile);
        }

        // Traitement du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un fichier image a été téléchargé
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($parcelle->getImage()) {
                    $oldImagePath = $this->getParameter('parcelle_images_directory') . '/' . $parcelle->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Générer un nouveau nom pour le fichier image
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Déplacer l'image dans le répertoire 'uploads/parcelles'
                    $imageFile->move(
                        $this->getParameter('parcelle_images_directory'),
                        $newFilename
                    );

                    // Enregistrer le nom du fichier image dans la base de données
                    $parcelle->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image.');
                }
            } else {
                // Si aucune nouvelle image n'est téléchargée, conserver l'ancienne image
                $parcelle->setImage($oldFileName);
            }

            // Sauvegarder les modifications dans la base de données
            $this->entityManager->flush();

            $this->addFlash('success', 'La parcelle a été modifiée avec succès!');
            return $this->redirectToRoute('parcelle_show', ['id' => $parcelle->getId()]);
        }

        return $this->render('parcelle/edit.html.twig', [
            'form' => $form->createView(),
            'parcelle' => $parcelle,
        ]);
    }

    #[Route('/parcelle/{id}/delete', name: 'parcelle_delete', methods: ['POST'])]
    public function delete(Request $request, Parcelle $parcelle): Response
    {
        // Vérifier le token CSRF pour la suppression
        if ($this->isCsrfTokenValid('delete' . $parcelle->getId(), $request->request->get('_token'))) {
            // Supprimer la parcelle de la base de données
            $this->entityManager->remove($parcelle);
            $this->entityManager->flush();

            // Ajouter un message flash pour indiquer le succès
            $this->addFlash('success', 'La parcelle a été supprimée avec succès.');
        }

        // Rediriger vers la liste des parcelles
        return $this->redirectToRoute('parcelle_index');
    }

    private function handleImageUpload(?UploadedFile $imageFile, Parcelle $parcelle, bool $isEdit): void
    {
        // Si un fichier image a été téléchargé
        if ($imageFile) {
            // Générer un nom de fichier unique
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            try {
                // Déplacer le fichier téléchargé dans le dossier d'images
                $imageFile->move($this->getParameter('parcelle_images_directory'), $newFilename);
                // Mettre à jour l'entité parcelle avec le nom du fichier image
                $parcelle->setImage($newFilename);
            } catch (FileException $e) {
                // Ajouter un message d'erreur en cas de problème avec l'upload
                $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
            }
        } elseif ($isEdit) {
            // Si nous sommes en mode édition et qu'aucune nouvelle image n'est téléchargée
            // Aucun changement à faire si l'image n'est pas modifiée
        }
    }
}
