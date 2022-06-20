<?php

namespace MediciVN\Core\Filters\Pipes;

use Closure;
use MediciVN\Core\Filters\FilterPipeContract;

class SortOfPipe implements FilterPipeContract
{
    public function handle(mixed $builder, Closure $next): mixed
    {
        $sortKey = request('sort_key', 'id');
        $sortValue = request('sort_value', 'desc');

        return $next($builder->orderBy($sortKey, $sortValue));
    }
}