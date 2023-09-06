<?php

namespace App\Tests\Functional\Controller\Abstract;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AbstractApiControllerTest extends WebTestCase
{
    const ENV_VAR_NAME_TEST_BANK_ACCOUNT_ID = "TEST_BANK_ACCOUNT_ID";
    const DEFAULT_TEST_BANK_ACCOUNT_ID = 1;

    const ENV_VAR_NAME_TEST_CUSTOMER_ID = "TEST_CUSTOMER_ID";
    const DEFAULT_TEST_CUSTOMER_ID = 1;

    protected function getBankAccountTestIdFromEnvVariables(): int {
        return (int) getenv(self::ENV_VAR_NAME_TEST_BANK_ACCOUNT_ID) ?? self::DEFAULT_TEST_BANK_ACCOUNT_ID;
    }

    protected function getCustomerTestIdFromEnvVariables(): int {
        return (int) getenv(self::ENV_VAR_NAME_TEST_CUSTOMER_ID) ?? self::DEFAULT_TEST_CUSTOMER_ID;
    }
}