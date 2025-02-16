<?php
// src/Enum/CategorieProduit.php
namespace App\Enum;

enum CategorieProduit: string
{
    case FRUITS = 'Fruits';
    case LEGUMES = 'Légumes';

    public static function getChoices(): array
    {
        return [
            'Fruits' => self::FRUITS->value,
            'Légumes' => self::LEGUMES->value,
        ];
    }
}
