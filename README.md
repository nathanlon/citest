## CI Customer & Bank Account API
Author: Nathan O'Hanlon

### Basic Details

A CRUD API for customers and their bank accounts.

### Running the App

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

### Assumptions
 - The update is using PATCH which means individual fields can be modified.

### API Documentation

[Documentation can be found here](https://documenter.getpostman.com/view/1541437/2s9Y5ePLCD)

### Still to do
- Setting a bank account as preferred should query the database to find if the same customer
already has a preferred bank account set. It should block saving with an error if this other account is found.

- Validation on the update, and generally the bank_account endpoint is not tested to work.
- A URL structure that has the customer within it, eg:
  - /api/customers/1/bank_accounts
- Validation that the bank account belongs to the customer
- Checking and wiring in the validation of the Mod11 validator (in a test)

## Tests

To run the tests, go to the / path and run:
````
php bin/phpunit
````

or if you have XDebug installed:
````
XDEBUG_CONFIG="idekey=PHPSTORM" bin/phpunit
````
In your php.ini file you will need to add:
````
[xdebug]
xdebug.idekey=PHPSTORM
xdebug.mode=debug 
````

To only run functional tests:
````
XDEBUG_CONFIG="idekey=PHPSTORM" bin/phpunit tests/Functional
````
To only run unit tests:
````
XDEBUG_CONFIG="idekey=PHPSTORM" bin/phpunit tests/Unit
````
To run all tests:
````
XDEBUG_CONFIG="idekey=PHPSTORM" bin/phpunit
````


### Functional tests

First create the database:
````
symfony console doctrine:database:create --env=test
````
Then run the migrations:
````
symfony console doctrine:migrations:migrate --env=test 
````

Then use the API to create entities first before querying them.

## Separation of Concerns

A lot of time was spent ensuring good separation from the Service layer so
that versioning could occur, and the API could be swapped out for another one or a web
interface and the service layer would not be effected.

Structure is basically:

Controller <> DAO <> Service <> Doctrine <> Database

The serialization of the request also took a fair amount of time to get right. I would
probably use a tool like [API Platform](https://api-platform.com/) to add all the extras
that are available such as ID and Resource URI detail baked in.




