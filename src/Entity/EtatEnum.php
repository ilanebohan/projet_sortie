<?php

namespace App\Entity;

enum EtatEnum : string
{
    case CREEE = 'Créée';
    case OUVERTE = 'Ouverte';
    case CLOTUREE = 'Clôturée';
    case ENCOURS = 'Activité en cours';
    case PASSEE = 'Passée';
    case ANNULEE = 'Annulée';
}

