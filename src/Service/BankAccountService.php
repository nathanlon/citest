<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Exception\ServiceException;
use App\Model\Abstract\AbstractModel;
use App\Model\Request\IncomingRequestModel;
use App\Model\Response\BankAccountCreatedModel;
use App\Model\Response\BankAccountListModel;
use App\Model\Request\BankAccountModel as BankAccountModelRequest;
use App\Model\Response\BankAccountModel as BankAccountModelResponse;
use App\Model\Response\OutgoingResponseModel;
use App\Repository\BankAccountRepository;
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
    ) {
    }

    public function create(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        try {
            $this->validateModel($incomingRequestModel);

            $bankAccountModel = $incomingRequestModel->getModel();

            $bankAccountEntity = $this->createBankAccountEntityFromModel($bankAccountModel);

            $this->entityManager->persist($bankAccountEntity);
            $this->entityManager->flush();

            //when created, simply returns the id of the created bank account.
            $bankAccountCreatedModel = new BankAccountCreatedModel();
            $bankAccountCreatedModel->setId($bankAccountEntity->getId());

            $outgoingResponse = new OutgoingResponseModel();
            $outgoingResponse->setStatusCode(self::STATUS_CODE_CREATED);
            $outgoingResponse->setModel($bankAccountCreatedModel);
        } catch (\Exception $exception) {
            throw new ServiceException(
                message: 'Unable to create bank account. An error occurred while saving.',
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

        $model = $incomingRequestModel->getModel();

        if (!empty($model->getAccountNumber())) {
            $bankAccountEntity->setAccountNumber($model->getAccountNumber());
        }
        if (!empty($model->getAccountType())) {
            $bankAccountEntity->setAccountType($model->getAccountType());
        }
        if (!empty($model->getAccountName())) {
            $bankAccountEntity->setAccountName($model->getAccountName());
        }
        if (!empty($model->getCurrency())) {
            $bankAccountEntity->setCurrency($model->getCurrency());
        }
        if (!empty($model->getIsPreferred())) {
            $bankAccountEntity->setIsPreferred($model->getIsPreferred());
        }

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
    public function getBankAccountEntityFromModel(IncomingRequestModel $incomingRequestModel): BankAccount
    {
        $id = $incomingRequestModel->getRouteParameters()['id'];

        $bankAccountEntity = $this->repository->find($id);

        if ($bankAccountEntity === null) {
            throw new ServiceException(sprintf("Bank account with id %d could not be found.", $id));
        }
        return $bankAccountEntity;
    }
}
