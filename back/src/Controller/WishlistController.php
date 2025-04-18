<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wishlist;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/wishlist', name: 'wishlist_')]
class WishlistController extends AbstractController
{
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Request $request, ProductRepository $productRepo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();

        $product = $productRepo->find($data['productId']);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $wishlist = $user->getWishlist();
        if (!$wishlist) {
            $wishlist = new Wishlist();
            $wishlist->setOwner($user);
            $em->persist($wishlist);
            $user->setWishlist($wishlist);
        }

        $wishlist->addProduct($product);
        $em->flush();

        return $this->json(['message' => 'Product added to wishlist']);
    }

    #[Route('/remove', name: 'remove', methods: ['POST'])]
    public function remove(Request $request, ProductRepository $productRepo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();
        $wishlist = $user->getWishlist();

        if (!$wishlist) {
            return $this->json(['error' => 'Wishlist not found'], 404);
        }

        $product = $productRepo->find($data['productId']);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $wishlist->removeProduct($product);
        $em->flush();

        return $this->json(['message' => 'Product removed from wishlist']);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $wishlist = $user->getWishlist();
        $products = $wishlist ? $wishlist->getProducts()->toArray() : [];

        return $this->json($products);
    }
}
