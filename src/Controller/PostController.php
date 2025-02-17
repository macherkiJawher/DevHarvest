<?php

namespace App\Controller;

use App\Form\CommentaireType;
use App\Entity\Commentaire;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;



#[Route('/post')]
class PostController extends AbstractController
{
    // Liste des posts
    #[Route('/', name: 'post_list')]
    public function listPosts(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([], ['date' => 'DESC']);
    
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }
    
    // Création d'un post
    #[Route('/new', name: 'post_create')]
    public function createPost(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Assigner l'agriculteur connecté comme auteur
            $post->setAuteur($this->getUser());
    
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
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
    
            $em->persist($post);
            $em->flush();
    
            return $this->redirectToRoute('post_list');
        }
    
        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    // Vue d'un post
    #[Route('/post/{id}', name: 'post_view')]
    public function view(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $commentaire->setDate(new \DateTime());
        $commentaire->setPost($post);
        // ASSIGNER l’utilisateur connecté comme auteur du commentaire
        $commentaire->setAuteur($this->getUser());

        $entityManager->persist($commentaire);
        $entityManager->flush();

        return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
    }

    return $this->render('post/view.html.twig', [
        'post' => $post,
        'form' => $form->createView(),
    ]);
}
    
    // Suppression d'un post
    #[Route('/delete/{id}', name: 'post_delete', methods: ['POST'])]
    public function deletePost(Post $post, EntityManagerInterface $em): Response
    {
        // Vérification que l'utilisateur connecté est l'auteur du post
        if ($post->getAuteur() !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez supprimer que vos propres posts.');
            return $this->redirectToRoute('post_list');
        }

        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Post supprimé avec succès.');
        return $this->redirectToRoute('post_list');
    }

    #[Route('/post/edit/{id}', name: 'post_edit')]
    public function editAction(Request $request, $id, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        // Récupérer le post à éditer
        $post = $em->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        // Vérification si l'utilisateur connecté est l'auteur du post
        if ($post->getAuteur() !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez modifier que vos propres posts.');
            return $this->redirectToRoute('post_list');
        }

        // Générer l'URL du formulaire d'édition
        $actionUrl = $this->generateUrl('post_edit', ['id' => $id]);
        // Créer le formulaire de modification
        $form = $this->createForm(PostType::class, $post, ['action' => $actionUrl]);

        // Récupérer l'image actuelle si elle existe et la convertir en un objet File
        $oldFileName = $post->getImage();
        $oldFileNamePath = $this->getParameter('post_images_directory') . '/' . $oldFileName;

        if ($oldFileName) {
            $pictureFile = new File($oldFileNamePath);
            $post->setImage($pictureFile);
        }

        // Traitement du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un fichier image a été téléchargé
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($post->getImage()) {
                    $oldImagePath = $this->getParameter('post_images_directory') . '/' . $post->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Générer un nouveau nom pour le fichier image
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Déplacer l'image dans le répertoire 'uploads/posts'
                    $imageFile->move(
                        $this->getParameter('post_images_directory'),
                        $newFilename
                    );

                    // Enregistrer le nom du fichier image dans la base de données
                    $post->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image.');
                }
            } else {
                // Si aucune nouvelle image n'est téléchargée, conserver l'ancienne image
                $post->setImage($oldFileName);
            }

            // Sauvegarder les modifications dans la base de données
            $em->flush();

            $this->addFlash('success', 'Post modifié avec succès!');
            return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }
}








