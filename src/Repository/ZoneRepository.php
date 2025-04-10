<?php

namespace App\Repository;

use App\Entity\Zone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Zone>
 */
class ZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    public function advancedSearch(?string $query, ?float $superficie, ?string $nomZone, ?string $localisationZone): array
    {
        $qb = $this->createQueryBuilder('z');
    
        // Rechercher par nom de zone ou localisation ou superficie
        if ($query) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('z.nom_zone', ':query'),
                $qb->expr()->like('z.localisation_zone', ':query')
            ))
            ->setParameter('query', '%' . $query . '%');
        }
    
        // Rechercher par superficie (si spécifiée)
        if ($superficie) {
            $qb->andWhere('z.superficie_zone = :superficie')
                ->setParameter('superficie', $superficie);
        }
    
        // Rechercher par nom de zone (si spécifié)
        if ($nomZone) {
            $qb->andWhere('z.nom_zone = :nomZone')
                ->setParameter('nomZone', $nomZone);
        }
    
        // Rechercher par localisation de zone (si spécifiée)
        if ($localisationZone) {
            $qb->andWhere('z.localisation_zone = :localisationZone')
                ->setParameter('localisationZone', $localisationZone);
        }
    
        // Retourner les résultats
        return $qb->getQuery()->getResult();
    }
    



    //    /**
    //     * @return Zone[] Returns an array of Zone objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('z')
    //            ->andWhere('z.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('z.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Zone
    //    {
    //        return $this->createQueryBuilder('z')
    //            ->andWhere('z.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
