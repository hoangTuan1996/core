<?php

namespace MediciVN\Core\Filters\Pipes;

use Closure;

class NamePipe extends BasePipe
{
    public function handle(mixed $builder, Closure $next): mixed
    {
        if (! request()->has($this->queryKey)) {
            return $next($builder);
        }

        $keyword = request($this->queryKey);

        return $next($builder->where('name', "LIKE", "%$keyword%"));
    }

    public function queryParam(): string
    {
        return 'name';
    }
}