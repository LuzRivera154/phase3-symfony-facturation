<?php

namespace App\Enum;

enum Unit: string
{
    case piece = 'piece';
    case hour = 'hour';
    case day = 'day';
    case month = 'month';
    case year = 'year';

    public function label(): string
    {
        return match($this) {
            Unit::piece => 'pièce',
            Unit::hour  => 'heure',
            Unit::day   => 'jour',
            Unit::month => 'mois',
            Unit::year  => 'an',
        };
    }
}
