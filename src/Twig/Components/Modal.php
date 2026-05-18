<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Modal
{
    public string $message = 'Confirmer cette action ?';
    public string $formAction = '';
    public string $csrfToken = '';
    public string $confirmLabel = 'Confirmer';
    public string $cancelLabel = 'Annuler';
    public string $modalId = '';
    public string $formId = '';

    public function mount(): void
    {
        if ($this->modalId === '') {
            $this->modalId = 'modal-' . uniqid();
        }
    }
}
