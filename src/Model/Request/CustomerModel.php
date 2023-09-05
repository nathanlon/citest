<?php

namespace App\Model\Request;

use App\Model\Interface\RequestModelInterface;
use App\Model\Trait\CustomerModelTrait;

class CustomerModel implements RequestModelInterface
{
    use CustomerModelTrait;
}
