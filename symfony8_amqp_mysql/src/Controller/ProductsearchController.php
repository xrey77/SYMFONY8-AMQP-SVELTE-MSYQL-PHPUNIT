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

final class ProductsearchController extends AbstractController
{

#[Route('/api/productsearch/{key}', name: 'app_productsearch', methods: ['GET'])]
public function getSearchProduct(
    string $key, 
    EntityManagerInterface $em,
    MessageBusInterface $bus,     
    SerializerInterface $serializer
): Response
{
    $search = '%' . strtolower($key) . '%';
    $qb = $em->getRepository(Product::class)->createQueryBuilder('p');        
    $qb->where($qb->expr()->like('LOWER(p.descriptions)', ':keyword'))
       ->setParameter('keyword', $search)
       ->orderBy('p.descriptions', 'ASC'); 

    $products = $qb->getQuery()->getResult();

    if (empty($products)) {
        return $this->json([
            'message' => 'Products not found'
        ], Response::HTTP_NOT_FOUND);
    }

    $productIds = array_map(fn($product) => $product->getId(), $products);

    $jsonContent = $serializer->serialize($products, 'json');

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

    // #[Route('/api/productsearch/{key}', name: 'app_productsearch', methods: ['GET'])]
    // public function getSearchProduct(
    //     string $key, EntityManagerInterface $em,
    //     SerializerInterface $serializer,
    //     ProductRepository $productRepository
    //     ): Response
    // {
    //     $search = '%' . strtolower($key) . '%';
    //     $qb = $em->getRepository(Product::class)->createQueryBuilder('p');        
    //     $qb->where($qb->expr()->like('LOWER(p.descriptions)', ':keyword'))
    //        ->setParameter('keyword', $search)
    //        ->orderBy('p.descriptions', 'ASC'); 

    //     $jsonContent = $serializer->serialize($qb->getQuery()->getResult(), 'json');


    //     return new Response(
    //         $jsonContent,
    //         Response::HTTP_OK,
    //         ['content-type' => 'application/json']
    //     );        
    // }


}
