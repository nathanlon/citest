<?php

namespace App\Controller;

use App\Dao\Interface\BankAccountsApiDaoInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class BankAccountApiController extends AbstractController
{
    public function __construct(
        private BankAccountsApiDaoInterface $dao
    ) {
    }

    #[Route('/bank_accounts', name: 'api_bank_accounts_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->dao->create($request);
    }

    #[Route('/bank_accounts', name: 'api_bank_accounts_read', defaults: ['limit' => 10, 'offset' => 0], methods: ['GET'])]
    public function read(Request $request): JsonResponse
    {
        return $this->dao->read($request);
    }

    #[Route('/bank_accounts/{id}', name: 'api_bank_accounts_read_one', methods: ['GET'])]
    public function readOne(int $id, Request $request): JsonResponse
    {
        return $this->dao->readOne($request);
    }

    #[Route('/bank_accounts/{id}', name: 'api_bank_account_update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        return $this->dao->update($request);
    }

    #[Route('/bank_accounts/{id}', name: 'api_bank_account_delete', methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        return $this->dao->delete($request);
    }
}
