<?php

namespace App\Repository;

use App\Entity\Machine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class MachineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Machine::class);
    }

    /**
     * Rechercher les machines par nom, type, marque ou état
     */
    public function searchMachines(?string $search): array
{
    $query = $this->createQueryBuilder('m');

    if ($search) {
        $query->andWhere('m.nomMachine LIKE :search OR m.type LIKE :search OR m.marque LIKE :search OR m.etat LIKE :search')
              ->setParameter('search', '%' . $search . '%');
    }

    return $query->getQuery()->getResult();
}


    /**
     * Récupérer les machines disponibles à la réservation (exclure celles du propriétaire connecté)
     */
    public function findMachinesForReservation(int $agriculteurId): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.proprietaire != :agriculteur')
            ->setParameter('agriculteur', $agriculteurId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les machines avec pagination
     */
    public function findPaginatedMachines(int $page, int $limit = 10)
    {
        $query = $this->createQueryBuilder('m')
            ->orderBy('m.nomMachine', 'ASC');

        return $this->paginate($query, $page, $limit);
    }

    /**
     * Fonction pour gérer la pagination
     */
    private function paginate(QueryBuilder $query, int $page, int $limit)
    {
        $offset = ($page - 1) * $limit;
        return $query
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    
}
