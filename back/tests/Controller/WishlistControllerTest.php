<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;


class WishlistControllerTest extends WebTestCase
{
    use TestTrait;

    private $client;
    private $jwt;
    private $em;
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

    public function testAddAndRemoveProductFromWishlist(): void
    {
        $this->client->request('POST', '/wishlist/add', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $this->jwt
        ], json_encode(['productId' => $this->productId]));

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString('{"message":"Product added to wishlist"}', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/wishlist', [], [], ['HTTP_Authorization' => 'Bearer ' . $this->jwt]);
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(self::DATA['name'], $this->client->getResponse()->getContent());

        $this->client->request('POST', '/wishlist/remove', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $this->jwt
        ], json_encode(['productId' => $this->productId]));

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString('{"message":"Product removed from wishlist"}', $this->client->getResponse()->getContent());
    }
}
