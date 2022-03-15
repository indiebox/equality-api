<?php

namespace App\Traits;

use App\Scopes\ClosingScope;

/**
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withClosed(bool $withClosed = true)
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder onlyClosed()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withoutClosed()
 */
trait Closable
{
    /**
     * Boot the closable trait for a model.
     *
     * @return void
     */
    public static function bootClosable()
    {
        static::addGlobalScope(new ClosingScope());
    }

    /**
     * Initialize the closable trait for an instance.
     *
     * @return void
     */
    public function initializeClosable()
    {
        if (! isset($this->casts[$this->getClosedAtColumn()])) {
            $this->casts[$this->getClosedAtColumn()] = 'datetime';
        }
    }

    /**
     * Close a model.
     *
     * @return bool|null
     */
    public function close()
    {
        $this->{$this->getClosedAtColumn()} = $this->freshTimestamp();
        $result = $this->save();

        if ($result) {
            $this->fireModelEvent('closed', false);
        }

        return $result;
    }

    /**
     * Open a closed model instance.
     *
     * @return bool|null
     */
    public function open()
    {
        $this->{$this->getClosedAtColumn()} = null;
        $result = $this->save();

        $this->fireModelEvent('opened', false);

        return $result;
    }

    /**
     * Determine if the model instance has been closed.
     *
     * @return bool
     */
    public function isClosed()
    {
        return ! is_null($this->{$this->getClosedAtColumn()});
    }

    /**
     * Register a "opened" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function opened($callback)
    {
        static::registerModelEvent('opened', $callback);
    }

    /**
     * Register a "closed" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function closed($callback)
    {
        static::registerModelEvent('closed', $callback);
    }

    /**
     * Get the name of the "closed at" column.
     *
     * @return string
     */
    public function getClosedAtColumn()
    {
        return defined('static::CLOSED_AT') ? static::CLOSED_AT : 'closed_at';
    }

    /**
     * Get the fully qualified "closed at" column.
     *
     * @return string
     */
    public function getQualifiedClosedAtColumn()
    {
        return $this->qualifyColumn($this->getClosedAtColumn());
    }
}
