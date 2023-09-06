# CI Customer & Bank Account API
Author: Nathan O'Hanlon

## Basic Details

A CRUD API for customers and their bank accounts.

## Running the App

- Check out the code from Git
- Using PHP 8.2.5 on your local machine, install the Symfony CLI.
- Install Docker and Docker compose
- Ensure port 5432 is open for communication with the database. Modify docker-compose.override.yml and the port number if required.
- Run:
  - symfony serve -d
  - docker-compose up -d
  - symfony console doctrine:database:create
  - symfony console doctrine:migrations:migrate
  - You can run SQL and check for some data with the command:
    - symfony console doctrine:query:sql 'SELECT * FROM customer'
    - symfony console doctrine:query:sql 'SELECT * FROM bank_account'

## Assumptions
 - The update is using PATCH which means individual fields can be modified.

## API Documentation

[Documentation can be found here](https://documenter.getpostman.com/view/1541437/2s9Y5ePLCD)

## Still to do
- More unit tests
- More functional tests
- Fixtures to set up functional tests
- Fixtures to load test data
- Multiple URI based resource and HTTP verbs
- HATEOS [See Richardson Maturity Model](https://www.geeksforgeeks.org/richardson-maturity-model-restful-api/)
- More specific exceptions
- Filters and Search parameters on endpoints
- Lock down the methods on each Interface
- More use of the Repository using Doctrine Query Buider
- Better error messages
- More abstraction of the service layer (eg: remove request & route params from IncomingRequestModel)
- Moving the PHP server into docker (not requiring local PHP setup, symfony & composer to run)
- Deleting of associated bank accounts when deleting a customer (untested)
- Versioning the API - with either headers or route changes
  - This should be simpler and was designed with this in mind, since the DAO layer can work out which version of multiple services to use.

# Tests

Because functional tests rely on the database and fixtures are not set yet, please run tests separately instead of with a bin/phpunit command directly.

## Functional Tests

### Setup

First create the database:
````
symfony console doctrine:database:create --env=test
````
Then run the migrations:
````
symfony console doctrine:migrations:migrate --env=test 
````

Then use the API to create entities first before querying them.

### Creating and finding records to use

There are currently no fixtures, so we need to make some records first to run tests with, then use these
records in environment variables for our tests to work with.

For each functional test, you will need to create a customer record first:
````
bin/phpunit tests/Functional/Controller/CustomerApiControllerTest.php --filter create
````
Then find the ID of the customer (left most number) using this command:
````
symfony console doctrine:query:sql 'SELECT * FROM customer' --env=test
````
Then use this ID when creating a bank_account:
````
TEST_CUSTOMER_ID=1 bin/phpunit tests/Functional/Controller/BankAccountApiControllerTest.php --filter create
````
Now find the new bank_account (left most number) using this command:
````
symfony console doctrine:query:sql 'SELECT * FROM bank_account' --env=test
````

### Running Functional Tests
After you have found some IDs to work with (see above), you can run the functional tests using instructions below

For a more specific match use the example (swapping out 2 ids and 'update'):
````
TEST_BANK_ACCOUNT_ID=1 TEST_CUSTOMER_ID=1 bin/phpunit tests/Functional/Controller/BankAccountApiControllerTest.php --filter '/::update$/'
TEST_BANK_ACCOUNT_ID=1 TEST_CUSTOMER_ID=1 bin/phpunit tests/Functional/Controller/CustomerApiControllerTest.php --filter '/::update$/'
````

To run just the non-delete functional tests, go to the root path of the repo and run:
````
TEST_BANK_ACCOUNT_ID=1 TEST_CUSTOMER_ID=1 bin/phpunit tests/Functional --testdox --filter '/::(create.*|read|readOne|update)$/'
````
Note: that the delete operation will wipe out the customer and bank_account record that you found.

Note: Using --testdox names the tests that have run.

## Unit Tests

To only run unit tests:
````
bin/phpunit tests/Unit
````

### Tests with XDebug

If you have XDebug installed, add another environment variable:
````
XDEBUG_CONFIG="idekey=PHPSTORM" bin/phpunit tests/Unit
````
In your php.ini file you will need to add:
````
[xdebug]
xdebug.idekey=PHPSTORM
xdebug.mode=debug 
````

# Separation of Concerns

A priority was ensuring good separation from the Service layer so
that versioning could occur, and the API could be swapped out for another one or a web
interface and the service layer would not be effected.

Structure is basically:

Controller <> DAO <> Service <> Doctrine <> Database

The serialization of the request (with validation) was important too, however I would
probably use a tool like [API Platform](https://api-platform.com/) to add all the extras
that are available such as ID and Resource URI detail baked in.
