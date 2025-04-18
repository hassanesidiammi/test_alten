<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

trait TestTrait
{
    const ADMIN_EMAIL = 'admin@admin.com';
    const ADMIN_PASS  = 'admin';

    const DATA = [
        'code' => 'ABCD123',
        'name' => 'Test Product ABCD123',
        'description' => 'Un produit ABCD123 Un produit ABCD123 Un produit ABCD123 ',
        'price' => 99.99,
        'quantity' => 10,
        'category' => 'Electronics',
        'image' => 'image.jpg',
        'internalReference' => 'REF123',
        'shellId' => 1,
        'inventoryStatus' => 'INSTOCK',
        'rating' => 5,
    ];

    protected function createUserTest($payload = [
        'username' => 'admin',
        'firstname' => self::ADMIN_EMAIL,
        'email' => 'admin@admin.com',
        'password' => self::ADMIN_PASS,
    ]): void
    {
        $userRepository = self::getContainer()->get('doctrine')->getManager()->getRepository(User::class);

        $existingUser = $userRepository->findOneBy(['email' => $payload['email']]);

        if (!$existingUser) {
            $this->client->request('POST', '/account', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        }
    }

    protected function getJwtToken($payload = [
        'email' => self::ADMIN_EMAIL,
        'password' => self::ADMIN_PASS,
    ]): string
    {
        $this->client->request('POST', '/token', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);

        return $this->jwt = $data['token'];
    }

    /**
     * Produit de test (utilisé dans d'autres tests)
     */
    protected function createProductForTest($payload = self::DATA): int
    {
        $this->client->request('POST', '/products', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $this->jwt
        ], json_encode(self::DATA));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
    }
}
