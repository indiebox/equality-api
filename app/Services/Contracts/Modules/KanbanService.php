<?php

namespace App\Services\Contracts\Modules;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;

interface KanbanService
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

    /**
     * Does user can move card to column.
     * This method checks for consistent moves:
     * None <-> ToDo <-> InProgress <-> OnReview? <-> Done
     * @param Card $card The card.
     * @param Column $column The column.
     * @return boolean
     */
    public function canMoveCardToColumn(Card $card, Column $column);
}
