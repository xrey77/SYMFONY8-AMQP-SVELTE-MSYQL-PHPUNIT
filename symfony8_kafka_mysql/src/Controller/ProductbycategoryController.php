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
use App\Entity\Category;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\ProductMessage;

final class ProductbycategoryController extends AbstractController
{
    #[Route('/api/report/product', name: 'product_report')]
    public function generateReport(
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

        $products = $productRepository->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getResult();        

        $groupedProducts = [];
        foreach ($products as $product) {
            $categoryName = $product->getCategory() ? $product->getCategory()->getName() : 'Uncategorized';
            $groupedProducts[$categoryName][] = $product;
        }

        $productIds = array_map(fn($product) => $product->getId(), $products);
 
        $html = $this->renderView('report/products.html.twig', [
            'groupedProducts' => $groupedProducts, // Pass the grouped array
            'logo_base64' => $base64,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        try {
            $bus->dispatch(new ProductMessage($productIds, 'productbycategory_success'));
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="report.pdf"'
        ]);
    }

}
