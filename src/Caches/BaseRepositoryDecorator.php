<?php

namespace MediciVN\Core\Caches;

use Closure;
use Throwable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use MediciVN\Core\Exceptions\MediciException;
use MediciVN\Core\Repositories\Eloquents\BaseRepository;
use MediciVN\Core\Repositories\Interfaces\RepositoryInterface;

abstract class BaseRepositoryDecorator implements RepositoryInterface
{
    protected Cache $cache;

    public function __construct(protected BaseRepository $repository)
    {
        $this->cache = new Cache(app('cache'));
        $this->cache->setGroup($cacheGroup ?? get_class($repository));
    }

    /**
     * @param string $function
     * @param array $args
     * @return mixed
     * @throws MediciException
     */
    public function getDataIfExistCache(string $function, array $args): mixed
    {
        if (! config('medici.enable_cache')) {
            return call_user_func_array([$this->repository, $function], $args);
        }

        try {
            $cacheKey = md5(
                static::class.
                $function.
                serialize(request()->input()).serialize(url()->current()).
                serialize(func_get_args())
            );

            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }

            $cacheData = call_user_func_array([$this->repository, $function], $args);

            // Store in cache for next request
            $this->cache->put($cacheKey, $cacheData);

            return $cacheData;
        } catch (Throwable $th) {
            dd($th);
            throw new MediciException($th->getCode(), $th->getMessage());
        }
    }

    /**
     * @param string $function
     * @param array $args
     * @return mixed
     */
    public function getDataWithoutCache(string $function, array $args): mixed
    {
        return call_user_func_array([$this->repository, $function], $args);
    }

    /**
     * @param string $function
     * @param array $args
     *
     * @return mixed
     * @throws MediciException
     */
    public function flushCacheAndUpdateData(string $function, array $args): mixed
    {
        try {
            $this->cache->flush();
            return call_user_func_array([$this->repository, $function], $args);
        } catch (Throwable $th) {
            throw new MediciException($th->getCode(), $th->getMessage());
        }
    }

    public function getModel()
    {
        return $this->repository->getModel();
    }

    public function resetModel()
    {
        return $this->repository->resetModel();
    }

    /**
     * @throws MediciException
     */
    public function find($id, array $columns = []): Model
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }

    /**
     * @throws MediciException
     */
    public function pluck($column, $key = null): Collection
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }

    /**
     * @throws MediciException
     */
    public function all(array $columns = []): Collection
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }

    /**
     * @throws MediciException
     */
    public function create(array $attributes): Model
    {
        return $this->flushCacheAndUpdateData(__FUNCTION__, func_get_args());
    }

    /**
     * @throws MediciException
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->flushCacheAndUpdateData(__FUNCTION__, func_get_args());
    }

    /**
     * @throws MediciException
     */
    public function delete($id): bool
    {
        return $this->flushCacheAndUpdateData(__FUNCTION__, func_get_args());
    }

    /**
     * @throws MediciException
     */
    public function update(array $attributes, $id): Model
    {
        return $this->flushCacheAndUpdateData(__FUNCTION__, func_get_args());
    }

    public function paginate(int $limit = 15, array $columns = ['*']): mixed
    {
        // TODO: Implement paginate() method.
    }

    public function simplePaginate(int $limit = 15, array $columns = ['*']): mixed
    {
        // TODO: Implement simplePaginate() method.
    }

    public function findByField(string $field, $value, array $columns = ['*']): Collection
    {
        // TODO: Implement findByField() method.
    }

    public function findWhere(array $where, array $columns = ['*']): Collection
    {
        // TODO: Implement findWhere() method.
    }

    public function findWhereIn(string $field, array $values, array $columns = ['*']): Collection
    {
        // TODO: Implement findWhereIn() method.
    }

    public function orderBy(string $column, string $direction = 'asc'): RepositoryInterface
    {
        // TODO: Implement orderBy() method.
    }

    public function with($relations): RepositoryInterface
    {
        // TODO: Implement with() method.
    }

    public function withCount($relations): RepositoryInterface
    {
        // TODO: Implement withCount() method.
    }

    public function scopeQuery(Closure $scope): RepositoryInterface
    {
        // TODO: Implement scopeQuery() method.
    }

    public function resetScope(): RepositoryInterface
    {
        // TODO: Implement resetScope() method.
    }

    public static function __callStatic(string $method, array $arguments): mixed
    {
        // TODO: Implement __callStatic() method.
    }

    public function __call(string $method, array $arguments): mixed
    {
        // TODO: Implement __call() method.
    }
}
