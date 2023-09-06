<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\Controller\Abstract\AbstractApiControllerTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class BankAccountApiControllerTest extends AbstractApiControllerTest
{
    /**
     * @test
     * NOTE: You will need to modify the TEST_CUSTOMER_ID environment variable below to a known
     * bank account in the test db. Pass it in with the command: TEST_CUSTOMER_ID=1 bin/phpunit
     * Use the command: symfony console doctrine:query:sql 'SELECT * FROM customer
     */
    public function create(): void
    {
        $customerId = $this->getBankAccountTestIdFromEnvVariables();

        $client = static::createClient();
        $client->request(method: Request::METHOD_POST,
            uri: '/api/bank_accounts',
            content: sprintf('{
                "account_number": "1120",
                "account_type": "ORGANIZATION",
                "account_name": "savings",
                "currency": "USD",
                "is_preferred": false,
                "customer_id": %d
            }', $customerId));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $arrayFromJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey("id", $arrayFromJson);
    }

    /**
     * @test
     * NOTE: You will need to modify the TEST_CUSTOMER_ID environment variable below to a known
     * bank account in the test db. Pass it in with the command: TEST_CUSTOMER_ID=1 bin/phpunit
     * Use the command: symfony console doctrine:query:sql 'SELECT * FROM customer
     */
    public function create_when_invalid_account_number(): void
    {
        $customerId = $this->getBankAccountTestIdFromEnvVariables();

        $client = static::createClient();
        $client->request(method: Request::METHOD_POST,
            uri: '/api/bank_accounts',
            content: sprintf('{
                "account_number": "124523",
                "account_type": "ORGANIZATION",
                "account_name": "savings",
                "currency": "USD",
                "is_preferred": false,
                "customer_id": %d
            }', $customerId));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertErrorMessageResponse(
            client: $client,
            message: "There were validation errors: The account_number is invalid (MOD11 required).  (Error code: 3)"
        );
    }

    /**
     * @test
     */
    public function create_when_invalid_account_type(): void
    {
        $customerId = $this->getBankAccountTestIdFromEnvVariables();

        $client = static::createClient();
        $client->request(method: Request::METHOD_POST,
            uri: '/api/bank_accounts',
            content: sprintf('{
                "account_number": "1120",
                "account_type": "SOMETHING_ELSE",
                "account_name": "savings",
                "currency": "USD",
                "is_preferred": false,
                "customer_id": %d
            }', $customerId));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertErrorMessageResponse(
            client: $client,
            message: "There were validation errors: The account_type must be either ORGANIZATION or PRIVATE.  (Error code: 3)"
        );
    }

    /**
     * @test
     */
    public function create_when_is_preferred_set_already(): void
    {
        $customerId = $this->getBankAccountTestIdFromEnvVariables();

        $client = static::createClient();
        $client->request(method: Request::METHOD_POST,
            uri: '/api/bank_accounts',
            content: sprintf('{
                "account_number": "1120",
                "account_type": "ORGANIZATION",
                "account_name": "savings",
                "currency": "USD",
                "is_preferred": true,
                "customer_id": %d
            }', $customerId));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertErrorMessageResponse(
            client: $client,
            message: "Customer already has a preferred bank account (Error code: 9)"
        );
    }

    /**
     * @test
     * To run by itself (due to readOne also being available), use a filter with exact match:
     * bin/phpunit tests/Functional/Controller/BankAccountApiControllerTest.php --filter '/::read$/'
     */
    public function read(): void
    {
        $client = static::createClient();
        $client->request(method: Request::METHOD_GET,
            uri: '/api/bank_accounts?limit=10&offset=0',
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $arrayFromJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('bank_accounts', $arrayFromJson);
        $bankAccounts = $arrayFromJson['bank_accounts'];
        $this->assertAllKeysExist($bankAccounts[0]);
    }

    /**
     * @test
     * To run by itself (due to readOne also being available), use a filter with exact match:
     * bin/phpunit tests/Functional/Controller/BankAccountApiControllerTest.php --filter '/::read_customers_bank_accounts$/'
     */
    public function read_customers_bank_accounts(): void
    {
        $customerId = $this->getCustomerTestIdFromEnvVariables();

        $client = static::createClient();
        $client->request(method: Request::METHOD_GET,
            uri: sprintf('/api/customers/%d/bank_accounts?limit=10&offset=0', $customerId),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $arrayFromJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('bank_accounts', $arrayFromJson);
        $bankAccounts = $arrayFromJson['bank_accounts'];
        $this->assertAllKeysExist($bankAccounts[0]);
    }

    /**
     * @TODO Make this TEST_BANK_ACCOUNT_ID environment variable come from fixtures.
     * NOTE: You will need to modify the TEST_BANK_ACCOUNT_ID environment variable below to a known
     *  bank account in the test db. Pass it in with the command: TEST_BANK_ACCOUNT_ID=1 bin/phpunit
     * Use the command: symfony console doctrine:query:sql 'SELECT * FROM bank_account
     * @test
     */
    public function readOne(): void
    {
        $bankAccountId = $this->getBankAccountTestIdFromEnvVariables();
        $client = static::createClient();
        $client->request(method: Request::METHOD_GET,
            uri: '/api/bank_accounts/'.$bankAccountId,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $arrayFromJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertAllKeysExist($arrayFromJson);
    }

    /**
     * @test
     */
    public function readOne_invalid_id_format(): void
    {
        $bankAccountId = "non-number";
        $client = static::createClient();
        $client->request(method: Request::METHOD_GET,
            uri: '/api/bank_accounts/'.$bankAccountId,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertErrorMessageResponse(
            client: $client,
            message: 'No route found for "GET http://localhost/api/bank_accounts/non-number"'
        );
    }

    /**
     * @TODO Make this TEST_BANK_ACCOUNT_ID environment variable come from fixtures.
     * NOTE: You will need to modify the TEST_BANK_ACCOUNT_ID environment variable below to a known
     *  bank account in the test db. Pass it in with the command: TEST_BANK_ACCOUNT_ID=1 bin/phpunit
     *  Use the command: symfony console doctrine:query:sql 'SELECT * FROM bank_account
     * @test
     */
    public function update(): void
    {
        $bankAccountId = $this->getBankAccountTestIdFromEnvVariables();
        $client = static::createClient();
        $client->request(method: Request::METHOD_PATCH,
            uri: '/api/bank_accounts/'.$bankAccountId,
            content: '{
                "account_number": "555X"
        }');

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    /**
     * @TODO Make this TEST_BANK_ACCOUNT_ID environment variable come from fixtures.
     * NOTE: You will need to modify the TEST_BANK_ACCOUNT_ID environment variable below to a known
     * bank account in the test db. Pass it in with the command: TEST_BANK_ACCOUNT_ID=1 bin/phpunit
     * Use the command: symfony console doctrine:query:sql 'SELECT * FROM bank_account' --env=test
     * @test
     */
    public function delete(): void
    {
        $bankAccountId = $this->getBankAccountTestIdFromEnvVariables();

        $client = static::createClient();
        $client->request(method: Request::METHOD_DELETE,
            uri: '/api/bank_accounts/'.$bankAccountId,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    private function assertAllKeysExist(array $bankAccount): void
    {
        $this->assertArrayHasKey("id", $bankAccount);
        $this->assertArrayHasKey("account_number", $bankAccount);
        $this->assertArrayHasKey("account_type", $bankAccount);
        $this->assertArrayHasKey("account_name", $bankAccount);
        $this->assertArrayHasKey("currency", $bankAccount);
        $this->assertArrayHasKey("is_preferred", $bankAccount);
        $this->assertArrayHasKey("customer_id", $bankAccount);
    }
}
