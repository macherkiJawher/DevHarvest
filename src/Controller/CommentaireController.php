<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commentaire')]
class CommentaireController extends AbstractController
{
    #[Route('/ajouter/{id}', name: 'ajouter_commentaire', methods: ['POST'])]
    public function ajouter(Post $post, Request $request, EntityManagerInterface $entityManager): Response
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
            'form' => $form->createView(),
        ]);
    }
}
