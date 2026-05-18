<?php

namespace App\Twig\Components;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class SearchProduit
{
    use DefaultActionTrait;
    #[LiveProp(writable: true)]
    public string $query = '';
    public function __construct(private ProductRepository $productRepository, private EntityManagerInterface $entityManagerInterface) {}
    public function getProducts(): array
    {
        return $this->productRepository->search($this->query);
    }
    #[LiveProp(writable: true)]
    public array $selectedItems = [];
    public function getItems(): array
    {
        return array_map(
            fn($item) => [
                'product' => $this->productRepository->find($item['product_id']),
                'quantity' => $item['quantity'],
            ],
            $this->selectedItems
        );
    }
    #[LiveAction]
    //#[LiveArg] viene el template y es necesario para recibir la informacion
    // Seria como el $_POST 
    //$_POST       → form submit   → controller lee el request
    //#[LiveArg]   → click AJAX    → Live Component inyecta el valor
    public function addProduct(#[LiveArg] int $id): void
    {
        // & modifica el elemento del array directamente.
        foreach ($this->selectedItems as &$item) {
            if ($item['product_id'] === $id) {
                $item['quantity']++;
                return;
            }
        }
        $this->selectedItems[] = ['product_id' => $id, 'quantity' => 1];
    }
    #[LiveAction]
    public function removeProduct(#[LiveArg] int $id): void
    {
        $this->selectedItems = array_filter(
            $this->selectedItems,
            fn($item) => $item['product_id'] !== $id
        );
    }

    #[LiveProp(writable: true)]
    public int $newQuantity = 1;
    #[LiveProp(writable: true)]
    public float $newPrice = 0;
    #[LiveAction]
    public function createAndAdd()
    {
        $product = new Product();
        $product->setName($this->query);
        $product->setPrice($this->newPrice);
        $this->entityManagerInterface->persist($product);
        $this->entityManagerInterface->flush();

        $this->selectedItems[] = ['product_id' => $product->getId(), 'quantity' => $this->newQuantity];
        $this->query = '';
        $this->newPrice = 0;
        $this->newQuantity = 1;
    }
    public function getTotal(): float
    {
        return array_sum(
            array_map(
                fn($item) => (float) $this->productRepository->find($item['product_id'])->getPrice() * $item['quantity'],
                $this->selectedItems
            )
        );
    }
}
