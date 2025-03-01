<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\RoleEnum;
use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;
use App\Entity\Culture;
use App\Form\CultureType;
use App\Entity\Parcelle;
use App\Form\ParcelleType;

#[Route('/admin')]
#[IsGranted('IS_AUTHENTICATED_FULLY')] 
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    #[Route('/', name: 'admin_dashboard')]
    public function index(): Response
    {
        $response = $this->render('admin/index.html.twig');

        // Désactiver le cache pour empêcher le retour arrière après déconnexion
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;

    }

    #[Route('/users', name: 'admin_users')]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/delete/{id}', name: 'admin_delete_user')]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/post', name: 'admin_post')]
    public function dashboard(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([], ['date' => 'DESC']);
    
        return $this->render('admin/post/list.html.twig', [
            'posts' => $posts,
        ]);
    }
     // Visualisation d'un post (détails)
     #[Route('/post/{id}', name: 'admin_post_view')]
     public function viewPost(Post $post): Response
     {
         return $this->render('admin/post/view.html.twig', [
             'post' => $post,
         ]);
     }
     
     // Modification d'un post par l'administrateur
    #[Route('/post/edit/{id}', name: 'admin_post_edit')]
public function editPostUser(
    Post $post,
    Request $request,
    EntityManagerInterface $em,
    SluggerInterface $slugger
): Response {
    // Vérification que l'utilisateur connecté est bien l'auteur du post
    if ($post->getAuteur() !== $this->getUser()) {
        $this->addFlash('danger', 'Vous ne pouvez modifier que vos propres posts.');
        return $this->redirectToRoute('post_list');
    }

    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'upload d'image si une nouvelle image est ajoutée
        $imageFile = $form->get('imageFile')->getData();
        if ($imageFile) {
            // Supprimer l'ancienne image si elle existe
            if ($post->getImage()) {
                $oldImagePath = $this->getParameter('post_images_directory') . '/' . $post->getImage();
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Générer un nouveau nom de fichier sécurisé
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('post_images_directory'),
                    $newFilename
                );
                $post->setImage($newFilename);
            } catch (FileException $e) {
                $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image.');
            }
        }

        $em->flush();

        $this->addFlash('success', 'Post modifié avec succès !');
        return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
    }

    return $this->render('post/edit.html.twig', [
        'form' => $form->createView(),
        'post' => $post,
    ]);
}

     // Suppression d'un post par l'administrateur
     #[Route('/post/delete/{id}', name: 'admin_post_delete', methods: ['POST'])]
     public function deletePost(Post $post, EntityManagerInterface $em): Response
     {
         $em->remove($post);
         $em->flush();
     
         $this->addFlash('success', 'Post supprimé avec succès.');
         return $this->redirectToRoute('admin_post');
     }

     #[Route('/comment/delete/{id}', name: 'admin_comment_delete', methods: ['POST'])]
    public function deleteComment(Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        $post = $commentaire->getPost();
        $em->remove($commentaire);
        $em->flush();
    
        $this->addFlash('success', 'Commentaire supprimé avec succès.');
        return $this->redirectToRoute('admin_post_view', ['id' => $post->getId()]);
    }
    #[Route('/admin/culture', name: 'admin_culture')]
    public function listCultures(EntityManagerInterface $em): Response
    {
        $cultures = $em->getRepository(Culture::class)->findAll();

        return $this->render('admin/culture/list.html.twig', [
            'cultures' => $cultures,
        ]);
    }

    #[Route('/admin/culture/new', name: 'admin_culture_new')]
    public function newCulture(Request $request, EntityManagerInterface $entityManager): Response
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

            return $this->redirectToRoute('admin_culture');
        }

        return $this->render('admin/culture/new.html.twig', [
            'culture' => $culture,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/culture/edit/{id}', name: 'admin_culture_edit')]
    public function editCulture(Culture $culture, Request $request, EntityManagerInterface $entityManager): Response
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
            return $this->redirectToRoute('admin_culture');
        }
    
        return $this->render('admin/culture/edit.html.twig', [
            'culture' => $culture,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/culture/delete/{id}', name: 'admin_culture_delete')]
    public function deleteCulture(Culture $culture, EntityManagerInterface $em): Response
    {
        $em->remove($culture);
        $em->flush();

        return $this->redirectToRoute('admin_culture');
    }
    #[Route('/admin/parcelle', name: 'admin_parcelle')]
    public function listParcelles(EntityManagerInterface $em): Response
    {
        $parcelles = $em->getRepository(Parcelle::class)->findAll();

        return $this->render('admin/parcelle/list.html.twig', [
            'parcelles' => $parcelles,
        ]);
    }

    #[Route('/admin/parcelle/new', name: 'admin_parcelle_new')]
    public function newParcelle(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parcelle = new Parcelle();
        $form = $this->createForm(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($parcelle);
            $entityManager->flush();

            return $this->redirectToRoute('admin_parcelle');
        }

        return $this->render('admin/parcelle/new.html.twig', [
            'parcelle' => $parcelle,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/parcelle/edit/{id}', name: 'admin_parcelle_edit')]
   public function edit(Request $request, Parcelle $parcelle): Response
    {
        // Vérification si l'utilisateur connecté est autorisé à éditer la parcelle
        

        // Créer l'URL du formulaire d'édition
        $actionUrl = $this->generateUrl('admin_parcelle_edit', ['id' => $parcelle->getId()]);
        
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
            return $this->redirectToRoute('admin_parcelle', ['id' => $parcelle->getId()]);
        }

        return $this->render('admin/parcelle/edit.html.twig', [
            'form' => $form->createView(),
            'parcelle' => $parcelle,
        ]);
    }

    #[Route('/admin/parcelle/delete/{id}', name: 'admin_parcelle_delete')]
    public function deleteParcelle(Parcelle $parcelle, EntityManagerInterface $em): Response
    {
        $em->remove($parcelle);
        $em->flush();

        return $this->redirectToRoute('admin_parcelle');
    }
}
