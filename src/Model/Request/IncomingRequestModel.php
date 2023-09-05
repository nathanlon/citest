<?php

namespace App\Model\Request;

use App\Model\Abstract\AbstractModel;
use App\Model\Interface\RequestModelInterface;

class IncomingRequestModel extends AbstractModel implements RequestModelInterface
{
    // @TODO combine route and request parameters into one array
    public function __construct(
        // private string $uri,
        // private array $headers, // @TODO put API version info & authorization in here.
        // private string $method,
        // private string $routeName,
        private array $routeParameters, //these are when the parameter is part of the route, eg /api/customers/1.
        private array $requestParameters, //these are when the URL contains ?param1=value1&param2=value2 etc.
        private string $crudKey, //a key such as Create, Read, ReadOne, Update or Delete.
        private ?RequestModelInterface $model
    ) {
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    public function setRouteParameters(array $routeParameters): void
    {
        $this->routeParameters = $routeParameters;
    }

    public function getRequestParameters(): array
    {
        return $this->requestParameters;
    }

    public function setRequestParameters(array $requestParameters): void
    {
        $this->requestParameters = $requestParameters;
    }

    public function getCrudKey(): string
    {
        return $this->crudKey;
    }

    public function setCrudKey(string $crudKey): void
    {
        $this->crudKey = $crudKey;
    }

    public function getModel(): ?RequestModelInterface
    {
        return $this->model;
    }

    public function setModel(?RequestModelInterface $model): void
    {
        $this->model = $model;
    }
}
