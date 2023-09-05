<?php

namespace App\Model\Response;

use App\Model\Interface\ResponseModelInterface;
use App\Model\Trait\IdTrait;

class BankAccountCreatedModel implements ResponseModelInterface
{
    use IdTrait;
}
