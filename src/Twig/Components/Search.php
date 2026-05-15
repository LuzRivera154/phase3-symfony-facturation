<?php

namespace App\Twig\Components;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Search
{
    use DefaultActionTrait;
    #[LiveProp(writable: true)]
    public string $status = 'all';

    public function __construct(private InvoiceRepository $invoice_repository) {}
    #findbystatus
    #[LiveAction]
    public function filterByStatus(#[LiveArg] string $status): void
    {
        $this->status = $status;
    }

    public function getInvoices(): array
    {
        if ($this->status === 'all') {
            return $this->invoice_repository->findAll();
        }
        return $this->invoice_repository->findByStatus($this->status);
    }
}
