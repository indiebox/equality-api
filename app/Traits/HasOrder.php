<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orderByPosition()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orderByPositionDesc()
 */
trait HasOrder
{
    /**
     * Initialize the has order trait for an instance.
     *
     * @return void
     */
    public function initializeHasOrder()
    {
        if (! isset($this->casts[$this->getOrderColumn()])) {
            $this->casts[$this->getOrderColumn()] = 'decimal:1';
        }
    }

    public function scopeOrderByPosition(Builder $query)
    {
        return $query->orderBy($this->getOrderColumn());
    }

    public function scopeOrderByPositionDesc(Builder $query)
    {
        return $query->orderByDesc($this->getOrderColumn());
    }

    /**
     * Move current model to the start.
     */
    public function moveToStart()
    {
        $this->{$this->getOrderColumn()} = 0.9;
        $this->save();

        $this->processNewOrder();
    }

    /**
     * Move current model to the end.
     */
    public function moveToEnd()
    {
        $column = $this->getOrderColumn();

        $query = static::query();
        $this->getOrderQuery($query);
        $lastItem = $query
            ->orderByPositionDesc()
            ->first(['order']);

        if ($lastItem == null) {
            return $this->moveToStart();
        }

        $this->{$column} = $lastItem->{$column} + 1;
        $this->save();
    }

    /**
     * Move current model after specified model.
     * @param Model $model
     * @return bool Returns `false` if model is not instance of current model.
     */
    public function moveAfter(Model $model)
    {
        if (!($model instanceof $this)) {
            return false;
        }

        $column = $this->getOrderColumn();
        $afterModelOrder = $model->{$column};

        $this->{$column} = $afterModelOrder + 0.1;
        $this->save();

        $this->processNewOrder();

        return true;
    }

    /**
     * Build additional order query.
     * It is a great place to filter elements by id of the list in which
     * models should be ordered.
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     */
    abstract public function getOrderQuery($query);

    /**
     * Get the name of the "order" column.
     *
     * @return string
     */
    public function getOrderColumn()
    {
        return defined('static::ORDER_COLUMN') ? static::ORDER_COLUMN : 'order';
    }

    /**
     * Recalculate the order of all elements filtered by `getOrderQuery`.
     * @return int The number of updated rows.
     */
    protected function processNewOrder()
    {
        $column = $this->getOrderColumn();

        $baseQuery = DB::table($this->getTable());
        $this->getOrderQuery($baseQuery);
        $baseQuery
            ->whereRaw(DB::raw("0 IN (@row_number:=0)"))
            ->orderByPosition();

        return $baseQuery->update([$column => DB::raw('(@row_number:=@row_number+1)')]);
    }
}
