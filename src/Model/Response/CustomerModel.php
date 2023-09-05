<?php

namespace App\Model\Response;

use App\Model\Interface\ResponseModelInterface;
use App\Model\Trait\CustomerModelTrait;
use App\Model\Trait\IdTrait;

class CustomerModel implements ResponseModelInterface
{
    use IdTrait;
    use CustomerModelTrait;
}
