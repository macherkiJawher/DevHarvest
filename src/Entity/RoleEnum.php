<?php

namespace App\Enum;

enum RoleEnum: string
{
    case AGRICULTEUR = 'ROLE_AGRICULTEUR';
    case CLIENT = 'ROLE_CLIENT';
    case FOURNISSEUR = 'ROLE_FOURNISSEUR';
    case TECHNICIEN = 'ROLE_TECHNICIEN';
    
    public static function getChoices(): array
    {
        return [
            'Agriculteur' => self::AGRICULTEUR,
            'Client' => self::CLIENT,
            'Fournisseur' => self::FOURNISSEUR,
            'Technicien' => self::TECHNICIEN,
        ];
    }
}
