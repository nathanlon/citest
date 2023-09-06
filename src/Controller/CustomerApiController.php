<?php

namespace App\Controller;

use App\Dao\Interface\CustomersApiDaoInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class CustomerApiController extends AbstractController
{
    public function __construct(
        private CustomersApiDaoInterface $dao
    ) {
    }

    #[Route('/customers', name: 'api_customers_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->dao->create($request);
    }

    #[Route('/customers', name: 'api_customers_read', defaults: ['limit' => 10, 'offset' => 0], methods: ['GET'])]
    public function read(Request $request): JsonResponse
    {
        return $this->dao->read($request);
    }

    #[Route('/customers/{id<\d+>}', name: 'api_customers_read_one', methods: ['GET'])]
    public function readOne(int $id, Request $request): JsonResponse
    {
        return $this->dao->readOne($request);
    }

    #[Route('/customers/{id<\d+>}', name: 'api_customer_update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        return $this->dao->update($request);
    }

    #[Route('/customers/{id<\d+>}', name: 'api_customer_delete', methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        return $this->dao->delete($request);
    }
}
