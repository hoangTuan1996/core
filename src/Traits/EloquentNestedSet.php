<?php

namespace MediciVN\Core\Traits;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Nested Set Model - hierarchies tree
 */
trait EloquentNestedSet
{
    /**
     * Get custom parent_id column name
     *
     * @return string
     */
    public static function parentIdColumn(): string
    {
        return defined(static::class . '::PARENT_ID') ? static::PARENT_ID : 'parent_id';
    }

    /**
     * Get custom right column name
     *
     * @return string
     */
    public static function rightColumn(): string
    {
        return defined(static::class . '::RIGHT') ? static::RIGHT : 'rgt';
    }

    /**
     * Get custom left column name
     *
     * @return string
     */
    public static function leftColumn(): string
    {
        return defined(static::class . '::LEFT') ? static::LEFT : 'lft';
    }

    /**
     * Get custom left column name
     *
     * @return string
     */
    public static function depthColumn(): string
    {
        return defined(static::class . '::DEPTH') ? static::DEPTH : 'depth';
    }

    /**
     * Get custom root's id value
     *
     * @return int
     */
    public static function rootId(): int
    {
        return defined(static::class . '::ROOT_ID') ? static::ROOT_ID : 1;
    }

    /**
     * Get queue connection
     *
     * @return string|null
     */
    public static function queueConnection(): string|null
    {
        return defined(static::class . '::QUEUE_CONNECTION') ? static::QUEUE_CONNECTION : null;
    }

    /**
     * Get queue
     *
     * @return string|null
     */
    public static function queue(): string|null
    {
        return defined(static::class . '::QUEUE') ? static::QUEUE : null;
    }

    /**
     * Put callback into queue if a queue connection is provided
     * Otherwise, run immediately
     *
     * @param Closure $callback
     * @return void
     */
    public static function instantOrQueue(Closure $callback): void
    {
        if (static::queueConnection() || static::queue()) {
            dispatch($callback)->onConnection(static::queueConnection())->onQueue(static::queue());
        } else {
            $callback();
        }
    }

    /**
     * Get table name
     *
     * @return string
     */
    public static function tableName(): string
    {
        return (new static)->getTable();
    }

    /**
     * get primary column name
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return (new static)->getKeyName();
    }

    /**
     * @return mixed
     */
    public static function rootNode(): mixed
    {
        return static::withoutGlobalScope('ignore_root')->find(static::rootId());
    }

    /**
     * Update tree when CRUD
     *
     * @return void
     * @throws Throwable
     */
    public static function booted(): void
    {
        // Ignore root node in global scope
        static::addGlobalScope('ignore_root', function (Builder $builder) {
            $builder->where(static::tableName() . '.' . static::primaryColumn(), '<>', static::rootId());
        });

        // set default parent_id is root's id
        static::saving(function (Model $model) {
            if (empty($model->{static::parentIdColumn()})) {
                $model->{static::parentIdColumn()} = static::rootId();
            }
        });

        static::created(function (Model $model) {
            static::instantOrQueue(function () use ($model) {
                $model->handleTreeOnCreated();
            });
        });

        static::updating(function (Model $model) {
            $oldParentId = $model->getOriginal(static::parentIdColumn());
            $newParentId = $model->{static::parentIdColumn()};

            if ($oldParentId != $newParentId) {
                // When run with queue, the new lft, rgt and parent_id will be assigned after calculation
                // so keep the old parent_id
                $model->{static::parentIdColumn()} = $oldParentId;

                static::instantOrQueue(function () use ($model, $newParentId) {
                    $model->handleTreeOnUpdating($newParentId);
                });
            }
        });

        static::deleting(function (Model $model) {
            static::instantOrQueue(function () use ($model) {
                $model->handleTreeOnDeleting();
            });
        });
    }

    /**
     * Scope a query to find ancestors.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAncestors($query)
    {
        return $query
            ->where(static::leftColumn(), '<', $this->{static::leftColumn()})
            ->where(static::rightColumn(), '>', $this->{static::rightColumn()});
    }

    /**
     * Scope a query to find descendants.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDescendants($query)
    {
        return $query
            ->where(static::leftColumn(), '>', $this->{static::leftColumn()})
            ->where(static::rightColumn(), '<', $this->{static::rightColumn()});
    }

    /**
     * Lấy tất cả các entity cha, sắp xếp theo thứ tự entity cha gần nhất đầu tiên.
     *
     * Các entity cha trong 1 cây sẽ có
     * - left nhỏ hơn left của entity hiện tại
     * - right lớn hơn right của entity hiện tại
     */
    public function getAncestors()
    {
        return $this->ancestors()->orderBy(static::leftColumn(), 'DESC')->get();
    }

    /**
     * Lấy tất cả các entity con
     *
     * Các entity con trong 1 cây sẽ có
     * - left lớn hơn left của entity hiện tại
     * - right nhỏ hơn right của entity hiện tại
     */
    public function getDescendants()
    {
        return $this->descendants()->get();
    }

    /**
     * The parent entity to which the current entity belongs
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, static::parentIdColumn());
    }

    /**
     * The children entity belongs to the current entity
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, static::parentIdColumn());
    }

    /**
     * Initial a query builder to interact with tree
     *
     * @param $parentId
     * @return Builder
     */
    public static function tree($parentId = null): Builder
    {
        $parentId = $parentId ?: static::rootId();
        $tableName = static::tableName();

        return static::query()
            ->selectRaw("$tableName.*")
            ->join("$tableName as parent", function ($join) use ($tableName, $parentId) {
                $join
                    ->on("$tableName." . static::leftColumn(), '>', 'parent.' . static::leftColumn())
                    ->on("$tableName." . static::leftColumn(), '<', 'parent.' . static::rightColumn())
                    ->where('parent.' . static::primaryColumn(), '=', $parentId);
            })
            ->orderBy("$tableName." . static::leftColumn());
    }

    /**
     * Build a nested tree
     *
     * @param Collection $nodes
     * @return Collection
     */
    public static function buildNestedTree(Collection $nodes): Collection
    {
        $tree = collect([]);
        $groupNodes = $nodes->groupBy(static::parentIdColumn());
        $tree->push(...$groupNodes->get(static::rootId()) ?? []);

        $getChildrenFunc = function ($tree) use (&$getChildrenFunc, $groupNodes) {
            foreach ($tree as $item) {
                $item->children = $groupNodes->get($item->id) ?: [];
                $getChildrenFunc($item->children);
            }
        };

        $getChildrenFunc($tree);
        return $tree;
    }

    /**
     * Get all nodes in nested array
     */
    public static function getTree(): Collection
    {
        $nodes = static::tree()->get();

        return static::buildNestedTree($nodes);
    }

    /**
     * Get all nodes order by parent-children relationship in flat array
     *
     * @return Collection
     */
    public static function getFlatTree(): Collection
    {
        return static::tree()->get();
    }

    /**
     * Get all parent in nested array
     *
     * @return Collection
     */
    public function getAncestorsTree(): Collection
    {
        return static::buildNestedTree($this->ancestors()->get());
    }

    /**
     * Get all descendants in nested array
     *
     * @return Collection
     */
    public function getDescendantsTree(): Collection
    {
        return static::buildNestedTree($this->descendants()->get());
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->{static::rightColumn()} - $this->{static::leftColumn()} + 1;
    }

    /**
     * Just save position fields of an instance even it has other changes
     * Position fields: lft, rgt, parent_id, depth
     *
     * @return Model
     */
    public function savePositionQuietly(): Model
    {
        static::query()
            ->where(static::primaryColumn(), '=', $this->{static::primaryColumn()})
            ->update([
                static::leftColumn() => $this->{static::leftColumn()},
                static::rightColumn() => $this->{static::rightColumn()},
                static::parentIdColumn() => $this->{static::parentIdColumn()},
                static::depthColumn() => $this->{static::depthColumn()},
            ]);

        return $this;
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function handleTreeOnCreated(): void
    {
        try {
            DB::beginTransaction();
            $node = static::find($this->id);
            $parent = static::withoutGlobalScope('ignore_root')->find($node->{static::parentIdColumn()});
            $parentRgt = $parent->{static::rightColumn()};

            // Node mới sẽ được thêm vào sau (bên phải) các nodes cùng cha
            $node->{static::depthColumn()} = $parent->{static::depthColumn()} + 1;
            $node->{static::leftColumn()} = $parentRgt;
            $node->{static::rightColumn()} = $parentRgt + 1;
            $width = $node->getWidth();

            // Cập nhật các node bên phải của node cha
            static::withoutGlobalScope('ignore_root')
                ->where(static::rightColumn(), '>=', $parentRgt)
                ->update([static::rightColumn() => DB::raw(static::rightColumn() . " + $width")]);

            static::query()
                ->where(static::leftColumn(), '>', $parentRgt)
                ->update([static::leftColumn() => DB::raw(static::leftColumn() . " + $width")]);

            $node->savePositionQuietly();
            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param $newParentId
     * @return void
     * @throws Throwable
     */
    public function handleTreeOnUpdating($newParentId): void
    {
        try {
            DB::beginTransaction();
            // Khi dùng queue, cần lấy lft và rgt mới nhất trong DB ra tính toán.
            $node = static::find($this->id);
            $currentLft = $node->{static::leftColumn()};
            $currentRgt = $node->{static::rightColumn()};
            $currentDepth = $node->{static::depthColumn()};
            $width = $node->getWidth();
            $query = static::withoutGlobalScope('ignore_root')->whereNot(static::primaryColumn(), $node->id);

            // Tạm thời để left và right các node con của node hiện tại ở giá trị âm
            $node->descendants()->update([
                static::leftColumn() => DB::raw(static::leftColumn() . " * (-1)"),
                static::rightColumn() => DB::raw(static::rightColumn() . " * (-1)"),
            ]);

            // Giả định node hiện tại bị xóa khỏi cây, cập nhật các node bên phải của node hiện tại
            (clone $query)
                ->where(static::rightColumn(), '>', $node->{static::rightColumn()})
                ->update([static::rightColumn() => DB::raw(static::rightColumn() . " - $width")]);

            (clone $query)
                ->where(static::leftColumn(), '>', $node->{static::rightColumn()})
                ->update([static::leftColumn() => DB::raw(static::leftColumn() . " - $width")]);

            // Tạo khoảng trống cho node hiện tại ở node cha mới, cập nhật các node bên phải của node cha mới
            $newParent = static::withoutGlobalScope('ignore_root')->find($newParentId);
            $newParentRgt = $newParent->{static::rightColumn()};

            (clone $query)
                ->where(static::rightColumn(), '>=', $newParentRgt)
                ->update([static::rightColumn() => DB::raw(static::rightColumn() . " + $width")]);

            (clone $query)
                ->where(static::leftColumn(), '>', $newParentRgt)
                ->update([static::leftColumn() => DB::raw(static::leftColumn() . " + $width")]);

            // Cập nhật lại node hiện tại theo node cha mới
            $node->{static::depthColumn()} = $newParent->{static::depthColumn()} + 1;
            $node->{static::parentIdColumn()} = $newParentId;
            $node->{static::leftColumn()} = $newParentRgt;
            $node->{static::rightColumn()} = $newParentRgt + $width - 1;
            $distance = $node->{static::rightColumn()} - $currentRgt;
            $depthChange = $node->{static::depthColumn()} - $currentDepth;

            // Cập nhật lại các node con có left và right âm
            static::query()
                ->where(static::leftColumn(), '<', 0 - $currentLft)
                ->where(static::rightColumn(), '>', 0 - $currentRgt)
                ->update([
                    static::leftColumn() => DB::raw("ABS(" . static::leftColumn() . ") + $distance"),
                    static::rightColumn() => DB::raw("ABS(" . static::rightColumn() . ") + $distance"),
                    static::depthColumn() => DB::raw(static::depthColumn() . " + $depthChange"),
                ]);

            $node->savePositionQuietly();
            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function handleTreeOnDeleting(): void
    {
        try {
            DB::beginTransaction();
            // make sure that no unsaved changes affect the calculation
            $this->refresh();

            // move the child nodes to the parent node of the deleted node
            $this->descendants()->update([
                static::parentIdColumn() => $this->{static::parentIdColumn()},
                static::leftColumn() => DB::raw(static::leftColumn() . " - 1"),
                static::rightColumn() => DB::raw(static::rightColumn() . " - 1"),
                static::depthColumn() => DB::raw(static::depthColumn() . " - 1"),
            ]);

            // Update the nodes to the right of the deleted node
            static::withoutGlobalScope('ignore_root')
                ->where(static::rightColumn(), '>', $this->{static::rightColumn()})
                ->update([static::rightColumn() => DB::raw(static::rightColumn() . " - 2")]);

            static::withoutGlobalScope('ignore_root')
                ->where(static::leftColumn(), '>', $this->{static::rightColumn()})
                ->update([static::leftColumn() => DB::raw(static::leftColumn() . " - 2")]);

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
