<?php

namespace App\Model\Request;

use App\Model\Interface\RequestModelInterface;
use App\Model\Trait\BankAccountModelTrait;

class BankAccountModel implements RequestModelInterface
{
    use BankAccountModelTrait;
}
