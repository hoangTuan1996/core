<?php

namespace MediciVN\Core\Filters\Pipes;

use MediciVN\Core\Filters\FilterPipeContract;

abstract class BasePipe implements FilterPipeContract
{
    protected string $queryKey;

    protected string $onField;

    public function __construct()
    {
        $this->queryKey = $this->queryParam();
    }

    abstract public function queryParam(): string;
}
