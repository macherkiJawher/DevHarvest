<?php
// src/Enum/TypeSol.php

namespace App\Enum;

enum TypeSol: string
{
    case ARGILEUX = 'argileux';
    case SABLEUX = 'sableux';
    case LIMONEUX = 'limoneux';
    case HUMIFÈRE = 'humifère';
    case ROCHEUX = 'rocheux';
    case CALCAIRE = 'calcaire';
    case LOAMEUX = 'loameux';
    case AUTRE = 'autre';
    case TOURBEUX = 'tourbeux';

    public static function getChoices(): array
    {
        return [
            'Argileux' => self::ARGILEUX->value,
            'Sableux' => self::SABLEUX->value,
            'Limoneux' => self::LIMONEUX->value,
            'Humifère' => self::HUMIFÈRE->value,
            'Rocheux' => self::ROCHEUX->value,
            'Calcaire' => self::CALCAIRE->value,
            'Loameux' => self::LOAMEUX->value,
            'Autre' => self::AUTRE->value,
            'Tourbeux' => self::TOURBEUX->value,
        ];
    }
}
