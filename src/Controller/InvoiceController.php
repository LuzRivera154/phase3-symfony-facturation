<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Enum\Status;
use App\Form\InvoiceType;
use App\Repository\InvoiceRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoice')]
final class InvoiceController extends AbstractController
{
    #[Route(name: 'app_invoice_index', methods: ['GET'])]
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoiceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_invoice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setNumber('FACT-' . $invoice->getCreatedAt()->format('Y') . '-' . str_pad($invoice->getCreatedAt()->format('n'), 3, '0', STR_PAD_LEFT));

            $invoice->setUser($this->getUser());

            $action = $request->request->get('invoice_action');
            $invoice->setStatus($action === 'validate' ? Status::pending_payment : Status::draft);

            $total = 0;
            foreach ($request->request->all('items') as $item) {
                $product = $productRepository->find($item['product_id']);
                $invoiceItem = new InvoiceItem();
                $invoiceItem->setProduct($product);
                $invoiceItem->setQuantity((int) $item['quantity']);
                $invoiceItem->setInvoice($invoice);
                $entityManager->persist($invoiceItem);
                $total += (float) $product->getPrice() * (int) $item['quantity'];
            }
            $invoice->setTotalTtc($total);

            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_show', methods: ['GET'])]
    public function show(Invoice $invoice): Response
    {
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/validate', name: 'app_invoice_validate', methods: ['POST'])]
    public function validate(Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $newStatus = match ($invoice->getStatus()) {
            Status::draft           => Status::pending_payment,
            Status::pending_payment => Status::paid,
            Status::paid            => Status::paid,
        };
        $invoice->setStatus($newStatus);
        $entityManager->flush();

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_delete', methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $invoice->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
    }
}
