<?php
// src/Controller/CalendrierController.php
namespace App\Controller;

use App\Entity\Evenement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CalendrierController extends AbstractController
{
    // Afficher la vue du calendrier
    #[Route('/calendrier', name: 'calendrier')]
    public function index(EntityManagerInterface $em)
    {
        // Récupérer tous les événements depuis la base de données
        $evenements = $em->getRepository(Evenement::class)->findAll();

        return $this->render('calendrier/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    // Récupérer les événements au format JSON pour FullCalendar
    #[Route('/evenements', name: 'evenements')]
    public function getEvenements(EntityManagerInterface $em)
    {
        // Récupérer les événements depuis la base de données
        $evenements = $em->getRepository(Evenement::class)->findAll();
        $data = [];

        // Préparer les événements pour FullCalendar
        foreach ($evenements as $evenement) {
            $data[] = [
                'title' => $evenement->getTitre(),
                'start' => $evenement->getDebut()->format('Y-m-d H:i:s'),
                'end' => $evenement->getFin() ? $evenement->getFin()->format('Y-m-d H:i:s') : null,
            ];
        }

        // Retourner les événements au format JSON
        return new JsonResponse($data);
    }
}
