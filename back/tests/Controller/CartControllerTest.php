<?php

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartControllerTest extends WebTestCase
{
    use TestTrait;

    private $client;
    private $em;
    private $jwt;
    private $productId;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->em->createQuery('delete ' . User::class . ' u where u.email = \'testuser@example.com\'')->execute();
        $this->em->createQuery('delete ' . User::class . ' u where u.email = \'' . self::ADMIN_EMAIL . '\'')->execute();
        $this->em->createQuery('delete ' . Product::class . ' p where p.code = \'' . self::DATA['code'] . '\'')->execute();

        $this->createUserTest();
        $this->createUserTest([
            'username' => 'test',
            'firstname' => 'user',
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);
        $this->getJwtToken();
        $this->productId = $this->createProductForTest();
        $this->getJwtToken([
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);
    }

    public function testGetCart()
    {

        $this->client->request('GET', '/cart', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->jwt,
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testAddProductToCart()
    {
        $this->client->request('POST', '/cart/add', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->jwt,
        ], json_encode([
            'productId' => $this->productId,
            'quantity' => 1,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAddOuofstockProductToCart()
    {
        $this->getJwtToken([
            'email' => self::ADMIN_EMAIL,
            'password' => self::ADMIN_PASS,
        ]);
        $productId = $this->createProductForTest([
            'code' => 'EF5251',
            'name' => 'Test Product EF5251',
            'description' => 'Un produit EF5251 Un produit EF5251 Un produit EF5251 ',
            'price' => 99.99,
            'quantity' => 10,
            'category' => 'Electronics',
            'image' => 'image.jpg',
            'internalReference' => 'REF123',
            'shellId' => 1,
            'inventoryStatus' => 'OUTOFSTOCK',
            'rating' => 5,
        ]);


        $this->getJwtToken([
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $this->client->request('POST', '/cart/add', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->jwt,
        ], json_encode([
            'productId' => $productId,
            'quantity' => 1,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('pas disponible.', $data['error']);
    }

    public function testRemoveProductFromCart()
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'testuser@example.com']);

        $product = new Product();
        $product->setCode('Product000001');
        $product->setInternalReference('Product000001');
        $product->setShellId('10001');
        $product->setCategory('test');
        $product->setName('Product 1');
        $product->setInventoryStatus('INSTOCK');
        $product->setPrice(100);
        $product->setQuantity(3);
        $product->setRating(4);

        $cart = new Cart();
        $cart->setOwner($user);
        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setProduct($product);
        $cartItem->setQuantity(1);

        $this->em->persist($product);
        $this->em->persist($cart);
        $this->em->persist($cartItem);
        $this->em->flush();

        $this->client->request('POST', '/cart/remove', [], [], ['HTTP_Authorization' => 'Bearer ' . $this->jwt,], json_encode([
            'productId' => $product->getId(),
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
