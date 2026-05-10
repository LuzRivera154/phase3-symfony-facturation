<?php

namespace App\Twig\Components;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Sidebar
{
    public function __construct(private Security $security) {}

    public function getUser(): ?User
    {
        return $this->security->getUser();
    }
}
