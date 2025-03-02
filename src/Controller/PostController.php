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
use App\Service\GeminiService;
use App\Service\ContentFilterService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'post_list')]
    public function listPosts(Request $request, PostRepository $postRepository): Response
    {
        // Récupérer le critère de tri depuis la requête (par défaut : date décroissante)
        $sortBy = $request->query->get('sort_by', 'date_desc');
        
        // Définir les options de tri disponibles
        $orderBy = [];
        switch ($sortBy) {
            case 'date_asc':
                $orderBy = ['date' => 'ASC'];
                break;
            case 'date_desc':
                $orderBy = ['date' => 'DESC'];
                break;
            case 'likes_asc':
                $orderBy = ['likes' => 'ASC'];
                break;
            case 'likes_desc':
                $orderBy = ['likes' => 'DESC'];
                break;
            case 'dislikes_asc':
                $orderBy = ['dislikes' => 'ASC'];
                break;
            case 'dislikes_desc':
                $orderBy = ['dislikes' => 'DESC'];
                break;
            default:
                $orderBy = ['date' => 'DESC']; // Par défaut
        }

        // Récupérer les posts avec le tri
        $posts = $postRepository->findBy([], $orderBy);

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'sort_by' => $sortBy, // Passer le critère actuel à la vue
        ]);
    }
    
    // Création d'un post
    #[Route('/new', name: 'post_create')]
    public function createPost(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        ContentFilterService $filterService
    ): Response {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $post->getTitre() . "\n" . $post->getContenu();

            // Filtrer le contenu avec Gemini en complément
            $filterResult = $filterService->filterContent($content, true);
            if ($filterResult['isInappropriate']) {
                $this->addFlash('danger', 'Votre post contient un contenu inapproprié et ne peut pas être publié.');
                return $this->render('post/create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            if ($filterResult['isOffTopic']) {
                $this->addFlash('danger', 'Votre post est hors sujet pour ce forum agricole et ne peut pas être publié.');
                return $this->render('post/create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Vérifier l'utilisateur connecté
            if (!$this->getUser()) {
                $this->addFlash('danger', 'Vous devez être connecté pour créer un post.');
                return $this->redirectToRoute('app_login');
            }
            $post->setAuteur($this->getUser());

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
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l’image : ' . $e->getMessage());
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

    #[Route('/vote/{id}/{type}', name: 'post_vote', methods: ['POST'])]
    public function vote(Post $post, string $type, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification du token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('vote' . $post->getId(), $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('post_view', ['id' => $post->getId()]));
        }

        // Récupérer la session et le tableau des votes
        $session = $request->getSession();
        $votes = $session->get('post_votes', []); // [postId => voteType] où voteType est 'like' ou 'dislike'

        $postId = $post->getId();
        $newVote = $type; // 'like' ou 'dislike'
        $oldVote = $votes[$postId] ?? null;

        if ($oldVote === $newVote) {
            // Même vote cliqué une deuxième fois → annuler le vote
            if ($newVote === 'like') {
                $post->decrementLikes();
            } else {
                $post->decrementDislikes();
            }
            unset($votes[$postId]);
            $this->addFlash('success', 'Votre vote a été annulé.');
        } elseif ($oldVote && $oldVote !== $newVote) {
            // L'utilisateur a déjà voté différemment → changer le vote
            if ($oldVote === 'like') {
                $post->decrementLikes();
            } else {
                $post->decrementDislikes();
            }
            if ($newVote === 'like') {
                $post->incrementLikes();
            } else {
                $post->incrementDislikes();
            }
            $votes[$postId] = $newVote;
            $this->addFlash('success', 'Votre vote a été mis à jour.');
        } else {
            // Aucun vote précédent → enregistrer le nouveau vote
            if ($newVote === 'like') {
                $post->incrementLikes();
            } else {
                $post->incrementDislikes();
            }
            $votes[$postId] = $newVote;
            $this->addFlash('success', 'Votre vote a été enregistré.');
        }

        // Enregistrer le tableau mis à jour dans la session
        $session->set('post_votes', $votes);
        $em->flush();

        // Redirection vers la page d'où provient la requête
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('post_view', ['id' => $post->getId()]));
    }

    #[Route('/post/{id}/auto-response', name: 'post_auto_response')]
public function autoResponse(Post $post, GeminiService $geminiService): Response
{
    try {
        // Concaténez le titre et le contenu pour envoyer à Gemini
        $postContent = $post->getTitre() . "\n" . $post->getContenu();
        $autoResponse = $geminiService->generateAutoResponse($postContent);

        return $this->render('post/auto_response.html.twig', [
            'post' => $post,
            'autoResponse' => $autoResponse,
        ]);
    } catch (\Exception $e) {
        $this->addFlash('danger', 'Erreur lors de la génération de la réponse : ' . $e->getMessage());
        return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
    }
}
#[Route('/chatbot', name: 'post_chatbot', methods: ['POST'])]
public function chatbot(Request $request, GeminiService $geminiService, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
{
    $token = $request->request->get('_csrf_token');
    if (!$this->isCsrfTokenValid('chatbot', $token)) {
        return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
    }

    try {
        $userMessage = $request->request->get('message');
        if (!$userMessage) {
            return new JsonResponse(['error' => 'Aucun message fourni'], 400);
        }

        $response = $geminiService->generateAutoResponse($userMessage);
        return new JsonResponse(['response' => $response]);
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Erreur lors de la génération de la réponse : ' . $e->getMessage()], 500);
    }
}
}








