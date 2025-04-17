<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/products')]
final class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'products_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $product = $this->serializer->deserialize($data, Product::class, 'json', [
            ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($product);
        $this->em->flush();

        return $this->json($product, Response::HTTP_CREATED);
    }

    #[Route('', name: 'products_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, (int) $request->query->get('limit', 10));
        $order = $request->query->get('order', 'asc');

        $repo = $this->em->getRepository(Product::class);
        $query = $repo->createQueryBuilder('p')
            ->orderBy('p.name', $order === 'desc' ? 'DESC' : 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $products = $query->getResult();

        return $this->json($products);
    }

    #[Route('/{id}', name: 'products_show', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        return $this->json($product);
    }

    #[Route('/{id}', name: 'products_update', methods: ['PATCH'])]
    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->getContent();
        $this->serializer->deserialize($data, Product::class, 'json', [
            ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            'object_to_populate' => $product,
        ]);

        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return $this->json($product);
    }

    #[Route('/{id}', name: 'products_delete', methods: ['DELETE'])]
    public function delete(Product $product): JsonResponse
    {
        $this->em->remove($product);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
