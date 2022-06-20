<?php

namespace MediciVN\Core\Repositories\Eloquents;

use Closure;
use MediciVN\Core\Enums\Conditions;
use MediciVN\Core\Enums\Operand;
use MediciVN\Core\Exceptions\RepositoryException;
use MediciVN\Core\Repositories\Interfaces\RepositoryInterface;
use MediciVN\Core\Traits\HasFilters;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository implements RepositoryInterface
{
    use HasFilters;

    protected Model|Builder $model;

    protected Closure|null $scopeQuery;

    public function __construct(protected Application $app)
    {
        $this->model = $this->makeModel();
    }

    /**
     * @return string
     */
    abstract public function model(): string;

    /**
     * @return Model
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function makeModel(): Model
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws BindingResolutionException
     */
    public function resetModel(): void
    {
        $this->makeModel();
    }

    /**
     * @param Closure $scope
     * @return $this
     */
    public function scopeQuery(Closure $scope): self
    {
        $this->scopeQuery = $scope;
        return $this;
    }

    /**
     * @param $column
     * @param $key
     * @return Collection
     */
    public function pluck($column, $key = null): Collection
    {
        return $this->model->pluck($column, $key);
    }

    /**
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        if ($this->model instanceof Builder) {
            return $this->resultParser($this->model->get($columns));
        }

        return $this->resultParser($this->model->all($columns));
    }

    /**
     * @param array $where
     * @param string $columns
     * @return int
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function count(array $where = [], string $columns = '*'): int
    {
        if ($where) {
            $this->applyConditions($where);
        }

        $result = $this->model->count($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * @param array $columns
     * @return Collection
     */
    public function get(array $columns = ['*']): Collection
    {
        return $this->all($columns);
    }

    /**
     * @param array $columns
     * @return Model
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function first(array $columns = ['*']): Model
    {
        $result = $this->model->first($columns);
        $this->resetModel();
        return $this->resultParser($result);
    }

    /**
     * @param int $limit
     * @param array $columns
     * @param string $method
     * @return mixed
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function paginate(int $limit = 15, array $columns = ['*'], string $method = 'paginate'): mixed
    {
        $result = $this->model->{$method}($limit, $columns);
        $this->resetModel();
        return $this->resultParser($result);
    }

    /**
     * @param int $limit
     * @param array $columns
     * @return mixed
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function simplePaginate(int $limit = 15, array $columns = ['*']): mixed
    {
        return $this->paginate($limit, $columns, 'simplePaginate');
    }

    /**
     * @param $id
     * @param array $columns
     * @return Model
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function find($id, array $columns = ['*']): Model
    {
        $model = $this->model->findOrFail($id, $columns);
        $this->resetModel();
        return $this->resultParser($model);
    }

    /**
     * @param string $field
     * @param null $value
     * @param array $columns
     * @return Collection
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function findByField(string $field, $value = null, array $columns = ['*']): Collection
    {
        $models = $this->model->where($field, '=', $value)->get($columns);
        $this->resetModel();
        return $this->resultParser($models);
    }

    /**
     * @param array $where
     * @param array $columns
     * @return Collection
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function findWhere(array $where, array $columns = ['*']): Collection
    {
        $this->applyConditions($where);
        $models = $this->model->get($columns);
        $this->resetModel();
        return $this->resultParser($models);
    }

    /**
     * @param string $field
     * @param array $values
     * @param array $columns
     * @return Collection
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function findWhereIn(string $field, array $values, array $columns = ['*']): Collection
    {
        $this->applyScope();
        $models = $this->model->whereIn($field, $values)->get($columns);
        $this->resetModel();
        return $this->resultParser($models);
    }

    /**
     * @param array $attributes
     * @return Model
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function create(array $attributes): Model
    {
        $model = $this->model->newInstance($attributes);
        $model->save();
        $this->resetModel();
        return $this->resultParser($model);
    }

    /**
     * @param array $attributes
     * @param $id
     * @return Model
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function update(array $attributes, $id): Model
    {
        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();
        $this->resetModel();
        return $this->resultParser($model);
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return Model
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        $model = $this->model->updateOrCreate($attributes, $values);
        $this->resetModel();
        return $this->resultParser($model);
    }

    /**
     * @param $id
     * @return bool
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function delete($id): bool
    {
        $model = $this->find($id);
        $this->resetModel();
        return $model->delete();
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->model = $this->model->orderBy($column, $direction);
        return $this;
    }

    /**
     * @param $relations
     * @return $this
     */
    public function with($relations): self
    {
        $this->model = $this->model->with($relations);
        return $this;
    }

    /**
     * @param $relations
     * @return $this
     */
    public function withCount($relations): self
    {
        $this->model = $this->model->withCount($relations);
        return $this;
    }

    /**
     * @param $result
     * @return mixed
     */
    public function resultParser($result): mixed
    {
        return $result;
    }

    /**
     * @param array $where
     * @return void
     * @throws RepositoryException
     */
    public function applyConditions(array $where): void
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {

                if (count($value) < 3) {
                    throw new RepositoryException("Format condition invalid");
                }

                $field = $value[0];
                $condition = $value[1];
                $val = $value[2];
                $operand = (isset($value[3]) ? isset($value[3]) : Operand::EQUAL)->name();

                switch ($condition) {
                    case Conditions::IN:
                        if (!is_array($val)) throw new RepositoryException("Input {$val} mus be an array");
                        $this->model = $this->model->whereIn($field, $val);
                        break;
                    case Conditions::NOT_IN:
                        if (!is_array($val)) throw new RepositoryException("Input {$val} mus be an array");
                        $this->model = $this->model->whereNotIn($field, $val);
                        break;
                    case Conditions::DATE:
                        $this->model = $this->model->whereDate($field, $operand, $val);
                        break;
                    case Conditions::DAY:
                        $this->model = $this->model->whereDay($field, $operand, $val);
                        break;
                    case Conditions::MONTH:
                        $this->model = $this->model->whereMonth($field, $operand, $val);
                        break;
                    case Conditions::YEAR:
                        $this->model = $this->model->whereYear($field, $operand, $val);
                        break;
                    case Conditions::EXISTS:
                        if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
                        $this->model = $this->model->whereExists($val);
                        break;
                    case Conditions::HAS:
                        if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
                        $this->model = $this->model->whereHas($field, $val);
                        break;
                    case Conditions::HAS_MORPH:
                        if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
                        $this->model = $this->model->whereHasMorph($field, $val);
                        break;
                    case Conditions::DOESNT_HAVE:
                        if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
                        $this->model = $this->model->whereDoesntHave($field, $val);
                        break;
                    case Conditions::DOESNT_HAVE_MORPH:
                        if (!($val instanceof Closure)) throw new RepositoryException("Input {$val} must be closure function");
                        $this->model = $this->model->whereDoesntHaveMorph($field, $val);
                        break;
                    case Conditions::BETWEEN:
                        if (!is_array($val)) throw new RepositoryException("Input {$val} mus be an array");
                        $this->model = $this->model->whereBetween($field, $val);
                        break;
                    case Conditions::BETWEEN_COLUMNS:
                        if (!is_array($val)) throw new RepositoryException("Input {$val} mus be an array");
                        $this->model = $this->model->whereBetweenColumns($field, $val);
                        break;
                    case Conditions::NOT_BETWEEN:
                        if (!is_array($val)) throw new RepositoryException("Input {$val} mus be an array");
                        $this->model = $this->model->whereNotBetween($field, $val);
                        break;
                    case Conditions::NOT_BETWEEN_COLUMNS:
                        if (!is_array($val)) throw new RepositoryException("Input {$val} mus be an array");
                        $this->model = $this->model->whereNotBetweenColumns($field, $val);
                        break;
                    case Conditions::RAW:
                        $this->model = $this->model->whereRaw($val);
                        break;
                    default:
                        $this->model = $this->model->where($field, $condition, $val);
                        break;
                }
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

    /**
     * @return $this
     */
    public function applyScope(): self
    {
        if (isset($this->scopeQuery) && is_callable($this->scopeQuery)) {
            $callback = $this->scopeQuery;
            $this->model = $callback($this->model);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function resetScope(): self
    {
        $this->scopeQuery = null;
        return $this;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return call_user_func_array([new static(), $method], $arguments);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments): mixed
    {
        return call_user_func_array([$this->model, $method], $arguments);
    }
}
