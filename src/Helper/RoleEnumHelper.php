<?php

namespace App\Helper;

use App\Enum\RoleEnum;

class RoleEnumHelper
{
    public static function getChoices(): array
    {
        return [
            'Agriculteur' => RoleEnum::AGRICULTEUR->value,
            'Client' => RoleEnum::CLIENT->value,
            'Fournisseur' => RoleEnum::FOURNISSEUR->value,
            'Technicien' => RoleEnum::TECHNICIEN->value,
        ];
    }
}
