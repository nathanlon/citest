<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\Controller\Abstract\AbstractApiControllerTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerApiControllerTest extends AbstractApiControllerTest
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
    public function create_when_ssn_already_set(): void
    {
        $duplicateSSN = rand(100000000, 999999999);

        //first request should be fine.
        $client = static::createClient();
        $client->request(method: Request::METHOD_POST,
            uri: '/api/customers',
            content: sprintf('{
                "first_name": "John",
                "last_name": "Doe",
                "ssn": "%d"
        }', $duplicateSSN));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $arrayFromJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey("id", $arrayFromJson);

        //second request with same SSN should fail
        $client->request(method: Request::METHOD_POST,
            uri: '/api/customers',
            content: sprintf('{
                "first_name": "John",
                "last_name": "Doe",
                "ssn": "%d"
        }', $duplicateSSN));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertErrorMessageResponse(
            client: $client,
            message: "Unable to create customer. SSN was already found. (Error code: 8)"
        );
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
        $this->assertArrayHasKey("customers", $arrayFromJson);
        $this->assertGreaterThan(0,$arrayFromJson['customers']);
        $firstCustomer = $arrayFromJson['customers'][0];
        $this->assertAllKeysExist($firstCustomer);
    }

    /**
     * @TODO Make this TEST_CUSTOMER_ID environment variable come from fixtures.
     * NOTE: You will need to modify the TEST_CUSTOMER_ID environment variable below to a known
     * customer in the test db. Pass it in with the command: TEST_CUSTOMER_ID=1 bin/phpunit
     * Use the command: symfony console doctrine:query:sql 'SELECT * FROM customer
     * @test
     */
    public function readOne(): void
    {
        $customerId = $this->getCustomerTestIdFromEnvVariables();
        $client = static::createClient();
        $client->request(method: Request::METHOD_GET,
            uri: '/api/customers/'.$customerId,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $arrayFromJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertAllKeysExist($arrayFromJson);
    }

    /**
     * @TODO Make this TEST_CUSTOMER_ID environment variable come from fixtures.
     * NOTE: This is a PATCH, so only fields entered will be changed.
     * NOTE: You will need to modify the TEST_CUSTOMER_ID environment variable below to a known
     * customer in the test db. Pass it in with the command: TEST_CUSTOMER_ID=1 bin/phpunit
     * Use the command: symfony console doctrine:query:sql 'SELECT * FROM customer
     * @test
     */
    public function update(): void
    {
        $customerId = $this->getCustomerTestIdFromEnvVariables();
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
     * @TODO Make this TEST_CUSTOMER_ID environment variable come from fixtures.
     * NOTE: You will need to modify the TEST_CUSTOMER_ID environment variable below to a known
     * customer in the test db. Pass it in with the command: TEST_CUSTOMER_ID=1 bin/phpunit
     * Use the command: symfony console doctrine:query:sql 'SELECT * FROM customer' --env=test
     * @test
     */
    public function delete(): void
    {
        $customerId = $this->getCustomerTestIdFromEnvVariables();

        $client = static::createClient();
        $client->request(method: Request::METHOD_DELETE,
            uri: '/api/customers/'.$customerId,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    private function assertAllKeysExist(array $customer): void
    {
        $this->assertArrayHasKey("id", $customer);
        $this->assertArrayHasKey("first_name", $customer);
        $this->assertArrayHasKey("last_name", $customer);
        $this->assertArrayHasKey("ssn", $customer);
    }
}
