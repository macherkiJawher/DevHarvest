<?php
// src/Controller/ProfileController.php
namespace App\Controller;

use App\Repository\PostRepository;
use App\Entity\User; // Assurez-vous d'importer votre entité User
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProfileController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}
    #[Route('/profile', name: 'user_profile')]
    public function index(PostRepository $postRepository): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour voir votre profil.");
        }

        // Récupérer uniquement les posts de l'utilisateur connecté
        $posts = $postRepository->findBy(['auteur' => $user]);

        return $this->render('profile/index.html.twig', [
            'posts' => $posts,
            'user' => $user,
        ]);
    }

    #[Route('/profile/{id}', name: 'profile')]
    public function profile(int $id, PostRepository $postRepository): Response
    {
        $user = $this->getUser(); // Utilisateur connecté

        // Vérifier si l'utilisateur existe
        $profileUser = $this->entityManager
            ->getRepository(User::class)
            ->find($id);

        if (!$profileUser) {
            throw $this->createNotFoundException('Utilisateur introuvable');
        }

        // Récupérer les posts de l'utilisateur sélectionné
        $posts = $postRepository->findBy(['auteur' => $profileUser]);

        return $this->render('profile/index.html.twig', [
            'posts' => $posts,
            'user' => $profileUser, // Passer l'utilisateur au template
        ]);
    }
}
