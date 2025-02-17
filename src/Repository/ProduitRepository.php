<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * Recherche des produits par catégorie.
     *
     * @param string $categorie
     * @return Produit[]
     */
    public function findByCategorie(string $categorie): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des produits en fonction d'un prix maximum.
     *
     * @param float $prixMax
     * @return Produit[]
     */
    public function findByPrixMax(float $prixMax): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.prixunitaire <= :prixMax')
            ->setParameter('prixMax', $prixMax)
            ->orderBy('p.prixunitaire', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des produits dont le nom contient un mot-clé.
     *
     * @param string $keyword
     * @return Produit[]
     */
    public function findByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nom LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche un produit par son ID.
     *
     * @param int $id
     * @return Produit|null
     */
    public function findOneById(int $id): ?Produit
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
