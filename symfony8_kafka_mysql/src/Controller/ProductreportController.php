<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Product;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\ProductMessage;

final class ProductreportController extends AbstractController
{

    #[Route('/api/productreport', name: 'app_productreport', methods: ['GET'])]
    public function generatePdf(
        ProductRepository $productRepository,
        MessageBusInterface $bus
        ): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isPhpEnabled', true); 
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        $path = $this->getParameter('kernel.project_dir') . '/public/images/logo.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    
        // $products = $productRepository->createQueryBuilder('p')
        //     ->leftJoin('p.category', 'c')
        //     ->addSelect('c')
        //     ->getQuery()
        //     ->getResult();        

        $products = $productRepository->findAll();

        $html = $this->renderView('productreport/product_detail.html.twig', [
            'products' => $products,
            'logo_base64' => $base64,
        ]);

        $productIds = array_map(fn($product) => $product->getId(), $products);


        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        try {
            $bus->dispatch(new ProductMessage($productIds, 'pdfreport_success'));
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="product_report.pdf"'
        ]);
    }


}
