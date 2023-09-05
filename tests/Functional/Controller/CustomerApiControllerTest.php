<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerApiControllerTest extends WebTestCase
{
    /** @test */
    public function create(): void
    {
        $client = static::createClient();
        $client->request(method: Request::METHOD_POST,
            uri: '/api/customers',
            content: sprintf('{
                "first_name": "John",
                "last_name": "Doe",
                "ssn": "%d"
        }', rand(100000000, 999999999)));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $arrayFromJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey("id", $arrayFromJson);
    }

    /** @test */
    public function read(): void
    {
        $client = static::createClient();
        $client->request(method: Request::METHOD_GET,
            uri: '/api/customers?limit=10&offset=0',
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $arrayFromJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey("id", $arrayFromJson);
        $this->assertArrayHasKey("first_name", $arrayFromJson);
    }

    /**
     * @TODO Make this parameter 1 come from fixtures.
     * NOTE: You will need to modify the $customerId value below to a known customer in the test db.
     * Use the command: symfony console doctrine:query:sql 'SELECT * FROM customer
     * @test
     */
    public function readOne(): void
    {
        $customerId = 1;
        $client = static::createClient();
        $client->request(method: Request::METHOD_GET,
            uri: '/api/customers/'.$customerId,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $arrayFromJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey("id", $arrayFromJson);
        $this->assertArrayHasKey("first_name", $arrayFromJson);
    }

    /**
     * @TODO Make this parameter 1 come from fixtures.
     * Note: This is a PATCH, so only fields entered will be changed.
     *  NOTE: You will need to modify the $customerId value below to a known customer in the test db.
     *  Use the command: symfony console doctrine:query:sql 'SELECT * FROM customer
     * @test
     */
    public function update(): void
    {
        $customerId = 1;
        $client = static::createClient();
        $client->request(method: Request::METHOD_PATCH,
            uri: '/api/customers/'.$customerId,
            content: sprintf('{
                "first_name": "John2",
                "ssn": "%d"
        }', rand(100000000, 999999999))
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    /**
     * @TODO Make this parameter 1 come from fixtures.
     * NOTE: You will need to modify the $customerId value below to a known customer in the test db.
     * Use the command: symfony console doctrine:query:sql 'SELECT * FROM customer' --env=test
     * @test
     */
    public function delete(): void
    {
        $customerId = 1;

        $client = static::createClient();
        $client->request(method: Request::METHOD_DELETE,
            uri: '/api/customers/'.$customerId,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
