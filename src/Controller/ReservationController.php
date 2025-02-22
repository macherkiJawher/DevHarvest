<?php
namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Machine;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/machine/{id}/reserver', name: 'app_machine_reserver')]
    public function reserver(Machine $machine, Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        $reservation = new Reservation();
        $reservation->setMachine($machine);
        $reservation->setClient($this->getUser());
        $reservation->setStatus('en attente');

        // Récupérer toutes les réservations existantes pour cette machine
        $reservations = $reservationRepository->findBy(['machine' => $machine]);

        $datesReservees = [];
        foreach ($reservations as $res) {
            $periode = new \DatePeriod(
                $res->getDateDebut(),
                new \DateInterval('P1D'),
                $res->getDateFin()->modify('+1 day') // Inclure le dernier jour
            );

            foreach ($periode as $date) {
                $datesReservees[] = $date->format('Y-m-d');
            }
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateDebut = $form->get('dateDebut')->getData();
            $dateFin = $form->get('dateFin')->getData();

            // Vérifier si la machine est déjà réservée pendant cette période
            foreach ($reservations as $existingReservation) {
                if (
                    ($dateDebut >= $existingReservation->getDateDebut() && $dateDebut <= $existingReservation->getDateFin()) ||
                    ($dateFin >= $existingReservation->getDateDebut() && $dateFin <= $existingReservation->getDateFin()) ||
                    ($dateDebut <= $existingReservation->getDateDebut() && $dateFin >= $existingReservation->getDateFin())
                ) {
                    // 🔴 Redirection vers la page affichant les dates réservées
                    return $this->redirectToRoute('app_machine_dates_reservees', ['id' => $machine->getId()]);
                }
            }

            // Si la machine est disponible, enregistrer la réservation
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation effectuée avec succès !');
            return $this->redirectToRoute('app_machine_list');
        }

        return $this->render('reservation/reserver.html.twig', [
            'machine' => $machine,
            'form' => $form->createView(),
            'datesReservees' => json_encode($datesReservees), // Envoi des dates réservées à la vue
        ]);
    }

    #[Route('/machine/{id}/dates-reservees', name: 'app_machine_dates_reservees')]
    public function datesReservees(Machine $machine, ReservationRepository $reservationRepository): Response
    {
        // Récupérer toutes les réservations existantes pour cette machine
        $reservations = $reservationRepository->findBy(['machine' => $machine]);

        $datesReservees = [];
        foreach ($reservations as $res) {
            $periode = new \DatePeriod(
                $res->getDateDebut(),
                new \DateInterval('P1D'),
                $res->getDateFin()->modify('+1 day')
            );

            foreach ($periode as $date) {
                $datesReservees[] = $date->format('Y-m-d');
            }
        }

        return $this->render('reservation/dates_reservees.html.twig', [
            'machine' => $machine,
            'datesReservees' => $datesReservees,
        ]);
    }
}
