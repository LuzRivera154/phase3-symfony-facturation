<?php

namespace App\Enum;

enum Status: string
{
    case draft = 'draft';
    case pending_payment = 'pending_payment';
    case paid = 'paid';

    public function label(): string
    {
        return match($this) {
            Status::draft => 'Brouillon',
            Status::pending_payment => 'En attente',
            Status::paid => 'Payées',
        };
    }
}