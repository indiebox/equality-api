<?php

namespace App\Rules\Api;

use App\Rules\Base\MaxItemsRule;

class MaxCardsPerColumn extends MaxItemsRule
{
    /** Max cards per column.*/
    public const MAX_ITEMS = 100;

    protected function getCount()
    {
        return $this->container->cards()->count();
    }

    protected function transKey()
    {
        return 'validation.max_cards_per_column';
    }
}
