<?php

namespace App\Enum;

enum EtatCommande: string
{
    case EN_ATTENTE = 'En attente';
    case VALIDEE = 'Validée';
    case EXPEDIEE = 'Expédiée';
    case LIVREE = 'Livrée';
    case ANNULEE = 'Annulée';
}
