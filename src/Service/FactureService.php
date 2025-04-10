<?php
namespace App\Service;

use Knp\Snappy\Pdf;
use Twig\Environment; // Remplacer EngineInterface par Twig\Environment

class FactureService
{
    private $pdf;
    private $twig;

    public function __construct(Pdf $pdf, Environment $twig) // Remplacer EngineInterface par Twig\Environment
    {
        $this->pdf = $pdf;
        $this->twig = $twig; // Utilisation de Twig\Environment
    }

    public function generateInvoice($commande): string
    {
        // Génère le HTML à partir d'une vue Twig
        $html = $this->twig->render('facture/invoice.html.twig', [
            'commande' => $commande,
        ]);

        // Génère le PDF à partir du HTML
        return $this->pdf->getOutputFromHtml($html);
    }
}
