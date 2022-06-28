<?php

namespace MediciVN\Core\Contracts;

use Throwable;

interface ShouldRenderExceptionRes
{
    public function renderExceptionResponse(Throwable $e): mixed;
}