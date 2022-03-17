<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder ordered()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orderedDesc()
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

    public function moveToStart()
    {
        $this->{$this->getOrderColumn()} = 0.9;
        $this->save();

        $this->processNewOrder();
    }

    public function moveToEnd()
    {
        $column = $this->getOrderColumn();

        $query = static::query();
        $this->getOrderQuery($query);

        $lastItem = $query
            ->orderByDesc($column)
            ->first(['order']);

        if ($lastItem == null) {
            return $this->moveToStart();
        }

        $this->{$column} = $lastItem->{$column} + 1;
        $this->save();
    }

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
    }

    public function getOrderQuery($query)
    {
        return null;
    }

    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy($this->getOrderColumn());
    }

    public function scopeOrderedDesc(Builder $query)
    {
        return $query->orderByDesc($this->getOrderColumn());
    }

    /**
     * Get the name of the "order" column.
     *
     * @return string
     */
    public function getOrderColumn()
    {
        return defined('static::ORDER_COLUMN') ? static::ORDER_COLUMN : 'order';
    }

    protected function processNewOrder()
    {
        $column = $this->getOrderColumn();

        $baseQuery = DB::table($this->getTable());
        $this->getOrderQuery($baseQuery);
        $baseQuery
            ->whereRaw(DB::raw("0 IN (@row_number:=0)"))
            ->orderBy($column);

        return $baseQuery->update([$column => DB::raw('(@row_number:=@row_number+1)')]);
    }
}
