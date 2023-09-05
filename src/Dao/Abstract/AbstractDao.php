<?php

namespace App\Dao\Abstract;

use App\Factory\Abstract\AbstractApiDaoFactory;
use App\Model\Api\Response\ApiErrorResponseModel;

abstract class AbstractDao
{
    public const FORMAT_JSON = "json";
    //public const ROUTER_ROUTE_KEY = '_route';
    public const ROUTER_ROUTE_PARAMS_KEY = '_route_params';
}
