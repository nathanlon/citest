<?php

namespace App\Model\Response;

use App\Model\Abstract\AbstractModel;
use App\Model\Interface\OutgoingResponseModelInterface;
use App\Model\Interface\ResponseModelInterface;

class OutgoingResponseModel extends AbstractModel implements OutgoingResponseModelInterface
{
    private int $statusCode;
    private ?ResponseModelInterface $model = null;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getModel(): ?ResponseModelInterface
    {
        return $this->model;
    }

    public function setModel(?ResponseModelInterface $model): void
    {
        $this->model = $model;
    }
}
