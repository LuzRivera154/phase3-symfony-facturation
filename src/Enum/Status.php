<?php

namespace App\Enum;

enum Status: string
{
    case draft = 'draft';
    case pending_payment = 'pending_payment';
    case paid = 'paid';
}