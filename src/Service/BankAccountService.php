<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Entity\Customer;
use App\Exception\ServiceException;
use App\Model\Abstract\AbstractModel;
use App\Model\Request\BankAccountModel;
use App\Model\Request\IncomingRequestModel;
use App\Model\Response\BankAccountCreatedModel;
use App\Model\Response\BankAccountListModel;
use App\Model\Request\BankAccountModel as BankAccountModelRequest;
use App\Model\Response\BankAccountModel as BankAccountModelResponse;
use App\Model\Response\OutgoingResponseModel;
use App\Repository\BankAccountRepository;
use App\Repository\CustomerRepository;
use App\Service\Abstract\AbstractService;
use App\Service\Interface\BankAccountServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BankAccountService extends AbstractService implements BankAccountServiceInterface
{
    public function __construct(
        protected ValidatorInterface $validator,
        protected LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private BankAccountRepository $repository,
        private CustomerRepository $customerRepository,
    ) {
    }

    private function checkPreferredBankAccountNotAlreadySetForCustomer(Customer $customer)
    {
        $existingPreferred = $this->repository->getCustomersPreferredBankAccount($customer);
        if ($existingPreferred !== null) {
            throw new ServiceException(
                message: "Customer already has a preferred bank account",
                code: self::STATUS_UNPROCESSABLE_ENTITY,
                internalCode: self::PREFERRED_BANK_ACCOUNT_ALREADY_SET_ERROR_CODE
            );
        }
    }

    /**
     * @throws ServiceException
     */
    public function create(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        try {
            $this->validateIncomingRequestModel($incomingRequestModel);

            $bankAccountModel = $incomingRequestModel->getModel();

            $bankAccountEntity = $this->createBankAccountEntityFromModel($bankAccountModel);

            // blocks creation if customer id is not correct.
            $customer = $this->findCustomerEntityFromId($bankAccountModel->getCustomerId());
            $bankAccountEntity->setCustomer($customer);

            if ($bankAccountModel->getIsPreferred()) {
                // find if any others are preferred for this customer.
                $this->checkPreferredBankAccountNotAlreadySetForCustomer($customer);
            }

            $this->entityManager->persist($bankAccountEntity);
            $this->entityManager->flush();

            //when created, simply returns the id of the created bank account.
            $bankAccountCreatedModel = new BankAccountCreatedModel();
            $bankAccountCreatedModel->setId($bankAccountEntity->getId());

            $outgoingResponse = new OutgoingResponseModel();
            $outgoingResponse->setStatusCode(self::STATUS_CODE_CREATED);
            $outgoingResponse->setModel($bankAccountCreatedModel);
        } catch (ServiceException $exception) {
            //exception may have been thrown specifically higher up the stack (eg customer not found).
            throw $exception;
        } catch (\Exception $exception) {
            throw new ServiceException(
                message: 'Unable to create bank account. An error occurred while saving.',
                code: self::STATUS_UNPROCESSABLE_ENTITY,
                internalCode: self::DATABASE_ERROR_CODE,
                previous: $exception
            );
        }

        return $outgoingResponse;
    }

    public function read(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        try {
            //@TODO Could put these into the read model so as to abstract these from the service.
            $limit = $incomingRequestModel->getRequestParameters()[AbstractModel::REQUEST_PARAM_LIMIT] ?? self::DEFAULT_LIMIT;
            $offset = $incomingRequestModel->getRequestParameters()[AbstractModel::REQUEST_PARAM_OFFSET] ?? self::DEFAULT_OFFSET;

            //@TODO Would put these into queries on the Repository.
            $bankAccountEntities = $this->repository->findBy(
                criteria: [],
                orderBy: [],
                limit: $limit,
                offset: $offset
            );

            $bankAccountModels = [];
            foreach ($bankAccountEntities as $bankAccountEntity) {
                $bankAccountModel = $this->createBankAccountModelFromEntity($bankAccountEntity);
                $bankAccountModels[] = $bankAccountModel;
            }

            $bankAccountListModel = new BankAccountListModel();
            $bankAccountListModel->setBankAccounts($bankAccountModels);

            $outgoingResponse = new OutgoingResponseModel();
            $outgoingResponse->setStatusCode(self::STATUS_CODE_OK);
            $outgoingResponse->setModel($bankAccountListModel);
        } catch (\Exception $exception) {
            throw new ServiceException(
                message: 'Unable to read bank accounts. An error occurred while retrieving.',
                code: self::STATUS_UNPROCESSABLE_ENTITY,
                internalCode: self::UNKNOWN_ERROR_CODE,
                previous: $exception
            );
        }

        return $outgoingResponse;
    }

    public function readOne(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        $statusCode = self::STATUS_CODE_OK;
        try {
            $bankAccountEntity = $this->getBankAccountEntityFromModel($incomingRequestModel);
            $model = $this->createBankAccountModelFromEntity($bankAccountEntity);
        } catch (ServiceException $exception) {
            //it's OK to have nothing, just modify the status code to 404.
            $statusCode = self::STATUS_CODE_NOT_FOUND;
            $model = null;
        }

        $outgoingResponse = new OutgoingResponseModel();
        $outgoingResponse->setStatusCode($statusCode);
        $outgoingResponse->setModel($model);

        return $outgoingResponse;
    }

    public function update(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        $bankAccountEntity = $this->getBankAccountEntityFromModel($incomingRequestModel);
        $originalModel = $this->getRequiredModel($incomingRequestModel);

        $completeModel = $this->populateMissingModelValuesFromEntity($originalModel, $bankAccountEntity);

        $this->validateModel($completeModel);

        $this->updateChangedModelValuesOntoExistingEntity($originalModel, $bankAccountEntity);

        $this->entityManager->flush();

        $outgoingResponse = new OutgoingResponseModel();
        $outgoingResponse->setStatusCode(self::STATUS_CODE_NO_CONTENT);

        return $outgoingResponse;
    }

    public function delete(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        $bankAccountEntity = $this->getBankAccountEntityFromModel($incomingRequestModel);

        $this->entityManager->remove($bankAccountEntity);
        $this->entityManager->flush();

        $outgoingResponse = new OutgoingResponseModel();
        $outgoingResponse->setStatusCode(self::STATUS_CODE_NO_CONTENT);

        return $outgoingResponse;
    }

    /**
     * Does not allow the customer id or id to be changed.
     */
    private function updateChangedModelValuesOntoExistingEntity(BankAccountModel $model, BankAccount $entity)
    {
        if (!empty($model->getAccountNumber())) {
            $entity->setAccountNumber($model->getAccountNumber());
        }
        if (!empty($model->getAccountType())) {
            $entity->setAccountType($model->getAccountType());
        }
        if (!empty($model->getAccountName())) {
            $entity->setAccountName($model->getAccountName());
        }
        if (!empty($model->getCurrency())) {
            $entity->setCurrency($model->getCurrency());
        }
        if (!empty($model->getIsPreferred())) {
            $entity->setIsPreferred($model->getIsPreferred());
        }
    }

    private function populateMissingModelValuesFromEntity(BankAccountModel $originalModel, BankAccount $entity): BankAccountModel
    {
        $model = clone($originalModel);

        if ($model->getAccountNumber() === null) {
            $model->setAccountNumber($entity->getAccountNumber());
        }
        if ($model->getAccountType() === null) {
            $model->setAccountType($entity->getAccountType());
        }
        if ($model->getAccountName() === null) {
            $model->setAccountName($entity->getAccountName());
        }
        if ($model->getCurrency() === null) {
            $model->setCurrency($entity->getCurrency());
        }
        if ($model->getIsPreferred() === null) {
            $model->setIsPreferred($entity->getIsPreferred());
        }
        if ($model->getCustomerId() === null) {
            $model->setCustomerId($entity->getCustomer()->getId());
        }
        return $model;
    }

    /**
     * @throws ServiceException
     */
    private function findCustomerEntityFromId(int $id): Customer
    {
        $customer = $this->customerRepository->find($id);
        if ($customer === null) {
            throw new ServiceException(
                message: sprintf("Customer id %d was not found.", $id),
                code: self::STATUS_CODE_NOT_FOUND,
                internalCode: self::CUSTOMER_ID_NOT_FOUND_ERROR_CODE,
            );
        }

        return $customer;
    }

    private function createBankAccountModelFromEntity(BankAccount $bankAccountEntity): BankAccountModelResponse
    {
        $bankAccountModel = new BankAccountModelResponse();
        $bankAccountModel->setId($bankAccountEntity->getId());
        $bankAccountModel->setAccountNumber($bankAccountEntity->getAccountNumber());
        $bankAccountModel->setAccountType($bankAccountEntity->getAccountType());
        $bankAccountModel->setAccountName($bankAccountEntity->getAccountName());
        $bankAccountModel->setCurrency($bankAccountEntity->getCurrency());
        $bankAccountModel->setIsPreferred($bankAccountEntity->getIsPreferred());
        $bankAccountModel->setCustomerId($bankAccountEntity->getCustomer()->getId());

        return $bankAccountModel;
    }

    private function createBankAccountEntityFromModel(BankAccountModelRequest $bankAccountModel): BankAccount
    {
        $bankAccountEntity = new BankAccount();
        $bankAccountEntity->setAccountNumber($bankAccountModel->getAccountNumber());
        $bankAccountEntity->setAccountType($bankAccountModel->getAccountType());
        $bankAccountEntity->setAccountName($bankAccountModel->getAccountName());
        $bankAccountEntity->setCurrency($bankAccountModel->getCurrency());
        $bankAccountEntity->setIsPreferred($bankAccountModel->getIsPreferred());

        return $bankAccountEntity;
    }

    /**
     * @throws ServiceException
     */
    private function getBankAccountEntityFromModel(IncomingRequestModel $incomingRequestModel): BankAccount
    {
        $id = $incomingRequestModel->getRouteParameters()['id'];

        $bankAccountEntity = $this->repository->find($id);

        if ($bankAccountEntity === null) {
            throw new ServiceException(
                message: sprintf("Bank account with id %d could not be found.", $id),
                code: self::STATUS_UNPROCESSABLE_ENTITY,
                internalCode: self::BANK_ACCOUNT_ID_NOT_FOUND_ERROR_CODE
            );
        }
        return $bankAccountEntity;
    }
}
