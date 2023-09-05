<?php

namespace App\Model\Response;

use App\Model\Interface\ResponseModelInterface;

class CustomerListModel implements ResponseModelInterface
{
    private array $customers;

    /**
     * @return CustomerModel[]
     */
    public function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * @param CustomerModel[] $customers
     */
    public function setCustomers(array $customers): void
    {
        $this->customers = $customers;
    }
}
