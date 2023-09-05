<?php

namespace App\Model\Response;

use App\Model\Interface\ResponseModelInterface;
use App\Model\Trait\BankAccountModelTrait;
use App\Model\Trait\IdTrait;

class BankAccountModel implements ResponseModelInterface
{
    use IdTrait;
    use BankAccountModelTrait;
}
