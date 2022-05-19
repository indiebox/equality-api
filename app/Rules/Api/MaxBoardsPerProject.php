<?php

namespace App\Rules\Api;

use App\Rules\Base\MaxItemsRule;

class MaxBoardsPerProject extends MaxItemsRule
{
    /** Max boards per project.*/
    public const MAX_ITEMS = 10;

    protected function getCount()
    {
        return $this->container->boards()->count();
    }

    protected function transKey()
    {
        return 'validation.max_boards_per_project';
    }
}
