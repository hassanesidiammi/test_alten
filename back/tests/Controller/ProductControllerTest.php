<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    use TestTrait;

    private $client;
    private $jwt = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        if (!$this->jwt) {
            $this->createUserTest();
            $this->jwt = $this->getJwtToken();
        }
    }

    public function testCreateProduct(): void
    {
        $this->client->request('POST', '/products', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $this->jwt
        ], json_encode(self::DATA));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testGetProducts(): void
    {
        $this->client->request('GET', '/products');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseFormatSame('json');
    }

    /**
     * Récupération d'un produit spécifique
     */
    public function testGetProduct(): void
    {
        // On crée un produit dans la base pour le test
        $id = $this->createProductForTest();

        // On récupère le produit par son ID (en supposant que l'ID est 1)
        $this->client->request('GET', '/products/' . $id);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseFormatSame('json');
    }

    /**
     * Création d'un produit avec des données invalides
     */
    public function testCreateProductWithInvalidData(): void
    {
        $data = [
            'code' => '', // Code vide
            'name' => '', // Nom vide
            'description' => '', // Description vide
            'price' => -10, // Prix invalide
            'quantity' => -3, // Quantité invalide
            'category' => 'Electronics',
            'image' => '',
            'internalReference' => 'REF123',
            'shellId' => 1,
            'inventoryStatus' => 'INSTOCK',
            'rating' => 5,
        ];

        $this->client->request('POST', '/products', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $this->jwt
        ], json_encode($data));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * Mise à jour d'un produit
     */
    public function testUpdateProduct(): void
    {
        $id = $this->createProductForTest();

        $updatedData = [
            'code' => 'ABCD123',
            'name' => 'Updated Product',
            'description' => 'Description mise à jour',
            'price' => 199.99,
            'quantity' => 15,
            'category' => 'Electronics',
            'image' => 'updated_image.jpg',
            'internalReference' => 'REF123',
            'shellId' => 1,
            'inventoryStatus' => 'INSTOCK',
            'rating' => 5,
        ];

        $this->client->request('PATCH', '/products/' . $id, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $this->jwt
        ], json_encode($updatedData));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDeleteProduct(): void
    {
        $id = $this->createProductForTest();
        $this->client->request('GET', '/products/' . $id, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->request('DELETE', '/products/' . $id, [], [], ['HTTP_Authorization' => 'Bearer ' . $this->jwt]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request('GET', '/products/' . $id);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Récupération d'un produit inexistant
     */
    public function testGetNonExistingProduct(): void
    {
        $this->client->request('GET', '/products/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Mise à jour d'un produit inexistant
     */
    public function testUpdateNonExistingProduct(): void
    {
        $this->client->request('PATCH', '/products/99999', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(self::DATA));

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Envoi de données non JSON
     */
    public function testCreateProductWithInvalidJsonFormat(): void
    {
        $data = 'code=ABCD123&name=Test+Product'; // Données en format URL-encoded

        $this->client->request('POST', '/products', [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_Authorization' => 'Bearer ' . $this->jwt
        ], $data);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
