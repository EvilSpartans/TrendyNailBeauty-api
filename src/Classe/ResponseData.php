<?php

namespace App\Classe;

class ResponseData
{
    private array $data;
    private int $status;

    public function __construct(array $data, int $status)
    {
        $this->data = $data;
        $this->status = $status;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}