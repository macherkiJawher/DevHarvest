<?php
namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Vérifier si une machine est disponible pour une période donnée
     */
    public function isMachineAvailable($machine, $dateDebut, $dateFin): bool
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.machine = :machine')
            ->andWhere(
                '(r.dateDebut BETWEEN :dateDebut AND :dateFin) OR
                 (r.dateFin BETWEEN :dateDebut AND :dateFin) OR
                 (:dateDebut BETWEEN r.dateDebut AND r.dateFin) OR
                 (:dateFin BETWEEN r.dateDebut AND r.dateFin)'
            )
            ->setParameter('machine', $machine)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult();

        return empty($qb); // Retourne `true` si la machine est disponible
    }
}
