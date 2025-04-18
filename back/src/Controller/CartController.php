<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Http\Attribute\IsGranted as AttributeIsGranted;

#[Route('/cart', name: 'cart_')]
#[AttributeIsGranted('IS_AUTHENTICATED_FULLY')]
final class CartController extends AbstractController
{
    #[Route('', name: 'get', methods: ['GET'])]
    public function getCart(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $cart = $user->getCart() ?? new Cart();
        return $this->json($cart, 200, [], ['groups' => 'cart:read']);
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addProductToCart(Request $request, EntityManagerInterface $em, ProductRepository $productRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        $cart = $user->getCart() ?? new Cart();
        if (!$user->getCart()) {
            $cart->setOwner($user);
            $em->persist($cart);
        }

        $product = $productRepo->find($data['productId']);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        foreach ($cart->getItems() as $item) {
            if ($item->getProduct() === $product) {
                $item->setQuantity($item->getQuantity() + $data['quantity']);
                $em->flush();
                return $this->json(['message' => 'Product quantity updated']);
            }
        }

        $item = new CartItem();
        $item->setCart($cart);
        $item->setProduct($product);
        $item->setQuantity($data['quantity']);
        $em->persist($item);
        $em->flush();

        return $this->json(['message' => 'Product added to cart']);
    }

    #[Route('/remove', name: 'remove', methods: ['POST'])]
    public function removeProductFromCart(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        $cart = $user->getCart();

        if (!$cart) {
            return $this->json(['error' => 'Cart is empty'], 400);
        }

        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() == $data['productId']) {
                $em->remove($item);
                $em->flush();
                return $this->json(['message' => 'Product removed from cart']);
            }
        }

        return $this->json(['error' => 'Product not found in cart'], 404);
    }
}
