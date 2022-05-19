<?php

namespace App\Services\Contracts\Boards;

use App\Models\Board;

interface ModuleService
{
    /**
     * Enable `Kanban` module for board.
     * @param Board $board The board.
     * @param array $settings The settings.
     */
    public function enableKanban(Board $board, array $settings);

    /**
     * Disable `Kanban` module for board.
     * @param Board $board The board.
     */
    public function disableKanban(Board $board);
}
