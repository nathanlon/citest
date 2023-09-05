<?php

namespace App\Repository;

use App\Entity\BankAccount;
use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankAccount>
 *
 * @method BankAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method BankAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method BankAccount[]    findAll()
 * @method BankAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BankAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccount::class);
    }

    public function getCustomersPreferredBankAccount(Customer $customer): ?BankAccount
    {
        //@TODO refactor to use query builder if more complex than this.
        $bankAccounts = $this->findBy(
            criteria: [
                'customer' => $customer,
                'isPreferred' => true
            ],
            orderBy: [],
            limit: 1,
            offset: 0
        );
        return $bankAccounts[0] ?? null;
    }
}
