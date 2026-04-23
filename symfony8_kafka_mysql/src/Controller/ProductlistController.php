<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Product;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\ProductMessage;

final class ProductlistController extends AbstractController
{
    #[Route('/api/productlist/{page}', name: 'app_productlist', methods: ['GET'])]
    public function getProducts(
        int $page,
        EntityManagerInterface $em,
        MessageBusInterface $bus,     
        SerializerInterface $serializer,
        ProductRepository $productRepository): Response
    {
        $perPage = 5;
        $offset = ($page - 1) * $perPage;
        $query = $em->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        // Use the Paginator
        $paginator = new Paginator($query);
        $paginator
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $items = iterator_to_array($paginator->getIterator());
        
        $totalItems = count($paginator); 

        if ($totalItems === 0) {
            return $this->json(['message' => 'Product(s) not found.'], 404);
        }

        $totpage = ceil($totalItems / $perPage);
        $totalpage = (int)$totpage;

        $productIds = array_map(fn($product) => $product->getId(), $items);

        $data = [
            'totalrecs' => $totalItems,
            'totpage' => $totalpage,
            'page' => $offset == 0 ? 1 : $page,
            'products' => $items,
        ];
        $jsonContent = $serializer->serialize($data, 'json');

        try {
            $bus->dispatch(new ProductMessage($productIds, 'productlist_success'));
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }


        return new Response(
            $jsonContent,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );        
    }
}