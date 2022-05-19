<?php

namespace App\Rules\Base;

use Illuminate\Contracts\Validation\ImplicitRule;

abstract class MaxItemsRule implements ImplicitRule
{
    public const MAX_ITEMS = 0;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected $container, protected $newItemsCount = 1)
    {
    }

    /**
     * Get current count of items.
     */
    abstract protected function getCount();

    /**
     * Get the key of the message for `trans` function.
     */
    abstract protected function transKey();

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->getCount() + $this->newItemsCount <= static::MAX_ITEMS;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans($this->transKey(), ['max' => static::MAX_ITEMS]);
    }
}
