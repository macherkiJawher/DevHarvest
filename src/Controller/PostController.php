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

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'post_list')]
    public function listPosts(PostRepository $postRepository): Response
    {
        // Récupérer tous les posts
        $posts = $postRepository->findBy([], ['date' => 'DESC']);
    
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }
    
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
    

    #[Route('/post/{id}', name: 'post_view')]
public function view(Post $post, Request $request, EntityManagerInterface $entityManager): Response
{
    $commentaire = new Commentaire();
    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $commentaire->setDate(new \DateTime());
        $commentaire->setPost($post);

        $entityManager->persist($commentaire);
        $entityManager->flush();

        return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
    }

    return $this->render('post/view.html.twig', [
        'post' => $post,
        'form' => $form->createView(), // ⚠️ On passe bien le formulaire à la vue
    ]);
}


    #[Route('/delete/{id}', name: 'post_delete', methods: ['POST'])]
public function deletePost(Post $post, EntityManagerInterface $em): Response
{
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
public function edit(Post $post, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    // Créer le formulaire avec l'entité Post existante
    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'upload d'image si une nouvelle image est ajoutée
        $imageFile = $form->get('imageFile')->getData();
        if ($imageFile) {
            // Supprimer l'ancienne image s'il y en avait une
            if ($post->getImage()) {
                $oldImagePath = $this->getParameter('post_images_directory') . '/' . $post->getImage();
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Supprimer l'ancienne image
                }
            }

            // Enregistrer la nouvelle image
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('post_images_directory'),
                    $newFilename
                );
                $post->setImage($newFilename); // Mettre à jour l'image du post
            } catch (FileException $e) {
                $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image.');
            }
        }

        // Persister l'entité modifiée et effectuer un flush pour enregistrer dans la base de données
        $em->persist($post);
        $em->flush();

        // Rediriger vers la page du post avec un message de succès
        $this->addFlash('success', 'Post modifié avec succès !');
        return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
    }

    return $this->render('post/edit.html.twig', [
        'form' => $form->createView(),
        'post' => $post,  // Passer l'objet post à la vue
    ]);
}






}
