<?php

namespace App\Model\Abstract;

/**
 * All models extend from here, and we can store constants related to the model here.
 * In a real project, there would be more abstraction.
 */
class AbstractModel
{
    public const CRUD_KEY_CREATE = "Create";
    public const CRUD_KEY_READ = "Read";
    public const CRUD_KEY_READ_ONE = "ReadOne";
    public const CRUD_KEY_UPDATE = "Update";
    public const CRUD_KEY_DELETE = "Delete";

    public const REQUEST_PARAM_LIMIT = 'limit';
    public const REQUEST_PARAM_OFFSET = 'offset';

    public const ROUTE_PARAM_CUSTOMER_ID = "customerId";
}
