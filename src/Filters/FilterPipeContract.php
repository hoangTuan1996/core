<?php

namespace MediciVN\Core\Filters;

use Closure;

interface FilterPipeContract
{
    /**
     * @param mixed $builder
     * @param Closure $next
     * @return mixed
     */
    public function handle(mixed $builder, Closure $next): mixed;
}
