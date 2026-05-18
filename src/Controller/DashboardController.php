<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Status;
use App\Repository\InvoiceRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(ProductRepository $productRepository, InvoiceRepository $invoiceRepository): Response
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $invoiceStatus = $user->getInvoices()->filter(
            fn($invoice) => $invoice->getStatus() === Status::pending_payment
        )->count();
        
        $totalClients = $user->getClients()->count();
        
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'user' => $user,
            'totalClients'  => $totalClients,
            'totalProducts' => $productRepository->count([]),
            'invoiceStatus' => $invoiceStatus,
            'cifraTotal'=>$invoiceRepository->totalTccPaided($user)

        ]);
    }
}
