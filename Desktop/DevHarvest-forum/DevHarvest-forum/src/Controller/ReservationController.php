<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Machine;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/machine/{id}/reserver', name: 'app_machine_reserver')]
    public function reserver(Machine $machine, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $reservation->setMachine($machine);
        $reservation->setClient($this->getUser());
        $reservation->setStatus('en attente');

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation effectuée avec succès !');
            return $this->redirectToRoute('app_machine_list', ['id' => $machine->getId()]);
        }

        return $this->render('reservation/reserver.html.twig', [
            'machine' => $machine,
            'form' => $form->createView(),
        ]);
    }
}