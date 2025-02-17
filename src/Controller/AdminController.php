<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\RoleEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/admin')]
#[IsGranted('IS_AUTHENTICATED_FULLY')] 
class AdminController extends AbstractController
{
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
}
