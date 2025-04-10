<?php
// src/Service/RendementService.php

namespace App\Service;

use App\Entity\Culture;
use App\Entity\Parcelle;
use Doctrine\ORM\EntityManagerInterface;

class RendementService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Calculer le rendement en fonction de la superficie totale des parcelles
    public function calculerRendement(Culture $culture): float
    {
        $parcelles = $culture->getParcelles();
        $rendementTotal = 0;

        foreach ($parcelles as $parcelle) {
            $superficie = $parcelle->getSuperficie(); // superficie de la parcelle en m²
            $prixDeLocation = $parcelle->getPrixDeLocation(); // prix de location de la parcelle

            // Par exemple, on peut définir une règle pour calculer le rendement en fonction de la superficie et du prix de location
            $rendementParcelle = $superficie * $prixDeLocation; // Ceci peut être ajusté pour des critères réels

            $rendementTotal += $rendementParcelle; // Ajouter le rendement de chaque parcelle
        }

        return $rendementTotal; // Retourner le rendement total
    }
}
