<?php

namespace App\Model\Response;

use App\Model\Interface\ResponseModelInterface;

class BankAccountListModel implements ResponseModelInterface
{
    private array $bankAccounts;

    /**
     * @return BankAccountModel[]
     */
    public function getBankAccounts(): array
    {
        return $this->bankAccounts;
    }

    /**
     * @param BankAccountModel[] $bankAccounts
     */
    public function setBankAccounts(array $bankAccounts): void
    {
        $this->bankAccounts = $bankAccounts;
    }
}
