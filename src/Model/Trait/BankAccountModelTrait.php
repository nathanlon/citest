<?php

namespace App\Model\Trait;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

trait BankAccountModelTrait
{
    #[Assert\Length(
        min: 2,
        max: 11,
        minMessage: "The account_number must be greater than {{ limit }} characters long.",
        maxMessage: "The account_number must be no more than {{ limit }} characters long."
    )]
    #[AppAssert\Mod11(message: "The account_number is invalid (MOD11 required).")]
    private string $accountNumber;

    #[Assert\Choice(choices: ["ORGANIZATION", "PRIVATE"], message: "The account_type must be either ORGANIZATION or PRIVATE.")]
    private string $accountType;

    #[Assert\NotBlank(message: "The account_name field is required.")]
    private string $accountName;

    #[Assert\Currency(message: "The currency must be a valid 3 character currency code (ISO 4217).")]
    private string $currency;

    #[Assert\Choice(choices: [true, false], message: "The is_preferred flag must be either true or false.")]
    private ?bool $isPreferred = null;

    //this can be blank because it will be set in the URL when created.
    // @TODO implement grouping of APIs.
    private int $customerId;

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function setAccountType(string $accountType): void
    {
        $this->accountType = $accountType;
    }

    public function getAccountName(): string
    {
        return $this->accountName;
    }

    public function setAccountName(string $accountName): void
    {
        $this->accountName = $accountName;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getIsPreferred(): ?bool
    {
        return $this->isPreferred;
    }

    public function setIsPreferred(?bool $isPreferred): void
    {
        $this->isPreferred = $isPreferred;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): void
    {
        $this->customerId = $customerId;
    }
}
