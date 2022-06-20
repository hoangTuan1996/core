<?php

namespace MediciVN\Core\Filters\Pipes;

use Closure;

class StatusPipe extends BasePipe
{
    public function handle(mixed $builder, Closure $next): mixed
    {
        if (! request()->has($this->queryKey)) {
            return $next($builder);
        }

        return $next($builder->where($this->queryKey, request($this->queryKey)));
    }

    public function queryParam(): string
    {
        return 'status';
    }
}