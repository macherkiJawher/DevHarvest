<?php
// src/Service/PaginationService.php
namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    public function paginate(QueryBuilder $queryBuilder, int $page, int $limit): array
    {
        $queryBuilder
            ->setFirstResult(($page - 1) * $limit)  // Début des résultats pour la page
            ->setMaxResults($limit);               // Nombre d'éléments par page

        return $queryBuilder->getQuery()->getResult();  // Retourner les résultats paginés
    }

    public function getPaginationDetails(QueryBuilder $queryBuilder, int $page, int $limit): array
    {
        $totalItems = count($queryBuilder->getQuery()->getResult());  // Compter le nombre total d'éléments
        $totalPages = ceil($totalItems / $limit);  // Calculer le nombre total de pages

        return [
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'current_page' => $page,
        ];
    }
}
