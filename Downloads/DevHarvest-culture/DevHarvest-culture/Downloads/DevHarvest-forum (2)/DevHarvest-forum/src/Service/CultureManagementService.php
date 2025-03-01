<?php
// src/Service/CultureManagementService.php
namespace App\Service;

use App\Entity\Culture;
use App\Entity\Parcelle;

class CultureManagementService
{
    public function canPlant(Culture $culture, Parcelle $parcelle): bool
    {
        // Vérifier si la saison de la culture correspond à la saison actuelle
        $currentSeason = $this->getCurrentSeason();
        if ($culture->getSaison() !== $currentSeason) {
            return false; // La culture n'est pas adaptée à la saison actuelle
        }

        // Vérifier si le type de sol de la parcelle est compatible avec la culture
        if (!$this->isSoilCompatible($culture, $parcelle)) {
            return false; // Le type de sol n'est pas compatible avec cette culture
        }

        return true; // La culture peut être plantée sur cette parcelle
    }

    private function getCurrentSeason(): string
    {
        $month = (int) date('m');
        if ($month >= 3 && $month <= 5) {
            return 'printemps';
        } elseif ($month >= 6 && $month <= 8) {
            return 'été';
        } elseif ($month >= 9 && $month <= 11) {
            return 'automne';
        } else {
            return 'hiver';
        }
    }

    private function isSoilCompatible(Culture $culture, Parcelle $parcelle): bool
    {
        // Exemple de logique basique de compatibilité du sol
        $soilCompatibility = [
            'tomate' => ['argileux', 'sableux'],
            'blé' => ['argileux'],
            'carotte' => ['sableux']
        ];

        return in_array($parcelle->getTypeSol(), $soilCompatibility[$culture->getNom()] ?? []);
    }
}
