<?php
// src/Enum/CategorieProduit.php
namespace App\Enum;

enum CategorieProduit: string
{
    case FRUITS = 'Fruits';
    case LEGUMES = 'Légumes';

    public static function getChoices(): array
    {
        // Return the enum values with user-friendly labels as keys
        return [
            'Fruits' => self::FRUITS->value,
            'Légumes' => self::LEGUMES->value,
        ];
    }

    // Optional: A method to get the labels of the enum
    public static function getLabels(): array
    {
        return [
            self::FRUITS->value => 'Fruits',
            self::LEGUMES->value => 'Légumes',
        ];
    }
    
    // Optional: A method to get all enum values as an array
    public static function getValues(): array
    {
        return [
            self::FRUITS->value,
            self::LEGUMES->value,
        ];
    }
}
