<?php

namespace App\Model\Trait;

use Symfony\Component\Validator\Constraints as Assert;

trait CustomerModelTrait
{
    #[Assert\NotBlank(message: "The first_name field is required.")]
    private ?string $firstName = null;

    #[Assert\NotBlank(message: "The last_name field is required.")]
    private ?string $lastName = null;

    #[Assert\NotBlank(message: "The ssn field is required.")]
    private ?string $ssn = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getSsn(): ?string
    {
        return $this->ssn;
    }

    public function setSsn(?string $ssn): void
    {
        $this->ssn = $ssn;
    }
}
