<?php

namespace App\Rules\Api;

use App\Rules\Base\MaxItemsRule;

class MaxColumnsPerBoard extends MaxItemsRule
{
    /** Max columns per board.*/
    public const MAX_ITEMS = 50;

    protected function getCount()
    {
        return $this->container->columns()->count();
    }

    protected function transKey()
    {
        return 'validation.max_columns_per_board';
    }
}
