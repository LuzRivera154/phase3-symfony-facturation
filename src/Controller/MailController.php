<?php

namespace App\Controller;

use App\Entity\Invoice;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\Processor\TempfileProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MailController extends AbstractController
{
    #[Route('/mail/{id}', name: 'app_mail')]
    public function index(Invoice $invoice, GotenbergPdfInterface $gotenberg): Response
    {
        $filePdf = $gotenberg->html()
            ->content('invoice/pdf.html.twig', ['invoice' => $invoice])
            ->addAsset('styles/pdf.css')
            ->processor(new TempfileProcessor())
            ->generate()
            ->process();

        return new Response(
            stream_get_contents($filePdf),
            200,
            ['Content-Type' => 'application/pdf']
        );
    }
}
